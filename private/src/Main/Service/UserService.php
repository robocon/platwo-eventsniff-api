<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/21/14
 * Time: 3:46 PM
 */

namespace Main\Service;


use Main\Context\Context;
use Main\DataModel\Image;
use Main\DB;
use Main\Exception\Service\ServiceException;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Main\Helper\URL;
use Main\Helper\UserHelper;
use Valitron\Validator;

class UserService extends BaseService {
    protected $fields = ["type", "display_name", "username", "email", "password", "gender", "birth_date", "picture", "mobile", "website", "fb_id", "fb_name", "type"];

    public function getCollection(){
        $db = DB::getDB();
        return $db->users;
    }
    
    public function getEventCollection(){
        $db = DB::getDB();
        return $db->event;
    }
    
    public function getSnifferCollection(){
        $db = DB::getDB();
        return $db->sniffer;
    }
    
    public function getGalleryCollection(){
        $db = DB::getDB();
        return $db->gallery;
    }

    public function add($params, Context $ctx){
        $allow = ["username", "email", "password", "gender", "country", "city", "birth_date"];
        $entity = ArrayHelper::filterKey($allow, $params);
        
        $v = new Validator($entity);
        $v->rule('required', ["username", "email", "password", "gender", "country", "city", "birth_date"]);
        $v->rule('email', ["email"]);
        $v->rule('lengthBetween', 'username', 4, 32);
        $v->rule('lengthBetween', 'password', 4, 32);
        $v->rule('in', 'gender', ['male', 'female', 'unspecify']);

        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $user_count = $this->getCollection()->find([
            '$or'=> [
                ['username' => $params['username']],
                ['email' => $params['email']]
            ]
        ])->count();
        
        if($user_count > 0){
            throw new ServiceException(ResponseHelper::validateError(['username'=> ['Duplicate username'], 'email'=> ['Duplicate email']]));
        }
        
        $entity['password'] = hash('sha256', $entity['password'].SITE_BLOWFISH);
        $entity['display_name'] = $entity['username'];
        $entity['birth_date'] = new \MongoDate(strtotime($entity['birth_date']));

        // set website,mobile to ''
        $entity['website'] = '';
        $entity['mobile'] = '';

        $entity['fb_id'] = '';
        $entity['fb_name'] = '';
        $entity['display_notification_number'] = 0;
        $entity['type'] = 'normal';

        // set default setting
        $entity['setting'] = UserHelper::defaultSetting();

        // register time
        $entity['created_at'] = new \MongoDate();

        // set default last login
        $entity['last_login'] = new \MongoDate();
        
        $entity['access_token'] = hash('sha256', SITE_BLOWFISH.uniqid());

        $this->getCollection()->insert($entity);
        unset($entity['password']);
        
        $entity['birth_date'] = MongoHelper::dateToYmd($entity['birth_date']);
        $entity['created_at'] = MongoHelper::dateToYmd($entity['created_at']);
        $entity['last_login'] = MongoHelper::dateToYmd($entity['last_login']);
        $entity['id'] = $entity['_id']->{'$id'};
        unset($entity['_id']);
        return $entity;
    }

    public function gets($options, Context $ctx){
        $default = array(
            "page"=> 1,
            "limit"=> 15
        );
        $options = array_merge($default, $options);

        $skip = ($options['page']-1)*$options['limit'];
        //$select = array("name", "detail", "feature", "price", "pictures");
        $condition = ['type'=> 'normal'];

        $cursor = $this->getCollection()
            ->find($condition, ['display_name', 'username', 'picture', 'email', 'fb_name', 'gender', 'created_at', 'last_login', 'picture'])
            ->limit($options['limit'])
            ->skip($skip)
            ->sort(array('created_at'=> -1));

        $total = $this->getCollection()->count($condition);
        $length = $cursor->count(true);

        $data = array();
        foreach($cursor as $item){
            $data[] = $item;
        }

        $res = [
            'length'=> $length,
            'total'=> $total,
            'data'=> $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        ];

        $pagingLength = $total/(int)$options['limit'];
        $pagingLength = floor($pagingLength)==$pagingLength? floor($pagingLength): floor($pagingLength) + 1;
        $res['paging']['length'] = $pagingLength;
        $res['paging']['current'] = (int)$options['page'];
        if(((int)$options['page'] * (int)$options['limit']) < $total){
            $nextQueryString = http_build_query(['page'=> (int)$options['page']+1, 'limit'=> (int)$options['limit']]);
            $res['paging']['next'] = URL::absolute('/user'.'?'.$nextQueryString);
        }
        return $res;
    }

    public function edit($id, $params, Context $ctx){
        $allow = ["email", "gender", "birth_date", "website", "mobile", "display_name", "username"];
        $set = ArrayHelper::filterKey($allow, $params);
        $v = new Validator($set);
        $v->rule('email', 'email');
        $v->rule('in', 'gender', ['male', 'female']);
//        $v->rule('date', 'birth_date');
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $old = $this->get($id, $ctx);

        if(isset($set['username'])){
            if($this->getCollection()->count(['username'=> $set['username']]) > 0 && $old['username'] != $set['username']){
                throw new ServiceException(ResponseHelper::error('Duplicate username'));
            }
        }

        if(isset($params['picture'])){
            $img = Image::upload($params['picture']);
            $set['picture'] = $img->toArray();
        }
        $set = ArrayHelper::ArrayGetPath($set);

        if(isset($set['birth_date'])){
            $set['birth_date'] = new \MongoTimestamp($set['birth_date']);
        }

        if(count($set)>0){
            $id = MongoHelper::mongoId($id);
            $this->getCollection()->update(['_id'=> $id], ['$set'=> $set]);
        }

        return $this->get($id, $ctx);
    }

    public function changePassword($id, $params, Context $ctx){
        $id = MongoHelper::mongoId($id);

        $v = new Validator($params);
        $v->rule('required', ['new_password', 'old_password']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $entity = $this->getCollection()->findOne(['_id'=> $id], ['password']);
        if(is_null($entity)){
            throw new ServiceException(ResponseHelper::notFound());
        }

        if((md5($params['old_password']) != $entity['password']) && isset($entity['password'])){
            throw new ServiceException(ResponseHelper::validateError(['old_password'=> ['Password not match']]));
        }

        $set = ['password'=> md5($params['new_password'])];
        $this->getCollection()->update(['_id'=> $id], ['$set'=> $set]);

        return ['success'=> true];
    }

    public function get($id, Context $ctx){
        $id = MongoHelper::mongoId($id);

        $fields = $this->fields;
        unset($fields['password']);

        $entity = $this->getCollection()->findOne(['_id'=> $id], $fields);
        if(is_null($entity)){
            throw new ServiceException(ResponseHelper::notFound());
        }

        MongoHelper::standardIdEntity($entity);

        if(isset($entity['picture'])){
            $entity['picture'] = Image::load($entity['picture'])->toArrayResponse();
        }
        else {
            
            // Load Default picture
            $entity['picture'] = Image::load([
                'id'=> '54297c9390cc13a5048b4567png',
                'width'=> 200,
                'height'=> 200
            ])->toArrayResponse();
        }
        
        $entity['birth_date'] = MongoHelper::dateToYmd($entity['birth_date']);
        if (empty($entity['detail'])) {
            $entity['detail'] = '';
        }
        
        return $entity;
    }

    public function me($params, Context $ctx){
        $v = new Validator($params);
        $v->rule('required', 'access_token');
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $tokenEntity = $this->getCollection()->findOne(['access_token'=> $params['access_token']]);
        if(is_null($tokenEntity)){
            throw new ServiceException(ResponseHelper::notAuthorize());
        }

        return $this->get($tokenEntity['_id'], $ctx);
    }

    public function requestResetCode($params, Context $ctx){
        $v = new Validator($params);
        $v->rule('required', ['email']);
        $v->rule('email', 'email');

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $user = $this->getCollection()->findOne(['email'=> $params['email']]);
        if(is_null($user)){
            throw new ServiceException(ResponseHelper::notFound());
        }

        $resetPasswordCode = md5(uniqid(MongoHelper::mongoId($user['_id']).'_reset', true));
        $this->getCollection()->update(['_id'=> $user['_id']], ['$set'=> ['reset_password_code'=> $resetPasswordCode]]);

        $strTo = $user['email'];
        $strSubject = "Thaweeyont Recovery Password.";
        $strHeader = "MIME-Version: 1.0' . \r\n";
        $strHeader .= "Content-type: text/html; charset=utf-8\r\n";
        $strHeader .= "From: Thaweeyont-service<admin@thaweeyont.com>";
        $strMessage = "You are code: ".$resetPasswordCode;
        if(isset($params['callback_url'])){
            $callback = parse_url($params['callback_url']);
            $url = 'http://'.$callback['host'].$callback['path'];
            $query = [];
            if(isset($callback['query'])){
                parse_str($callback['query'], $query);
            }
            $query['reset_password_code'] = $resetPasswordCode;
            $callback['query'] = http_build_query($query);
            $url .= '?'.$callback['query'];
            $strMessage .= <<<HTML
            <br />
or follow link: <a href="{$url}">{$url}</a>
HTML;
        }

        $flgSend = @mail($strTo, $strSubject, $strMessage, $strHeader);  // @ = No Show Error //
//            $flgSend = mail($strTo, $strSubject, $strMessage, $strHeader);  // @ = No Show Error //
        if(!$flgSend)
        {
            throw new ServiceException(ResponseHelper::error('Email not send.'));
        }

        return [
            'id'=> MongoHelper::standardId($user['_id']),
            'email'=> $user['email']
        ];
    }

    public function getUserByCode($params, Context $ctx){
        $v = new Validator($params);
        $v->rule('required', ['reset_password_code']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $user = $this->getCollection()->findOne(['reset_password_code'=> $params['reset_password_code']], ['username', 'display_name']);
        if(is_null($user)){
            throw new ServiceException(ResponseHelper::notFound());
        }

        return $user;
    }

    public function setPasswordByCode($params, Context $ctx){
        $v = new Validator($params);
        $v->rule('required', ['reset_password_code', 'new_password']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $user = $this->getCollection()->findOne(['reset_password_code'=> $params['reset_password_code']], ['username', 'display_name']);
        if(is_null($user)){
            throw new ServiceException(ResponseHelper::notFound());
        }

        $newPassword = md5($params['new_password']);
        $this->getCollection()->update(['_id'=> $user['_id']], ['$set'=> ['password'=> $newPassword], '$unset'=> ['reset_password_code'=> '']]);

        return true;
    }

    public function getAdmins($options){
        $default = array(
            "page"=> 1,
            "limit"=> 15
        );
        $options = array_merge($default, $options);

        $skip = ($options['page']-1)*$options['limit'];
        //$select = array("name", "detail", "feature", "price", "pictures");
        $condition = ['type'=> ['$in'=> ['admin', 'super_admin']]];

        $cursor = $this->getCollection()
            ->find($condition, ['display_name', 'username', 'picture', 'email', 'fb_name', 'gender', 'created_at', 'last_login', 'picture', 'type'])
            ->limit($options['limit'])
            ->skip($skip)
            ->sort(array('created_at'=> -1));

        $total = $this->getCollection()->count($condition);
        $length = $cursor->count(true);

        $data = array();
        foreach($cursor as $item){
            $data[] = $item;
        }

        $res = [
            'length'=> $length,
            'total'=> $total,
            'data'=> $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        ];

        $pagingLength = $total/(int)$options['limit'];
        $pagingLength = floor($pagingLength)==$pagingLength? floor($pagingLength): floor($pagingLength) + 1;
        $res['paging']['length'] = $pagingLength;
        $res['paging']['current'] = (int)$options['page'];
        if(((int)$options['page'] * (int)$options['limit']) < $total){
            $nextQueryString = http_build_query(['page'=> (int)$options['page']+1, 'limit'=> (int)$options['limit']]);
            $res['paging']['next'] = URL::absolute('/user'.'?'.$nextQueryString);
        }
        return $res;
    }

    public function addAdmin($params, Context $ctx){
        $allow = ["username", "email", "password", "gender", "birth_date"];
        $entity = ArrayHelper::filterKey($allow, $params);

        $v = new Validator($entity);
        $v->rule('required', ["username", "email", "password"]);
        $v->rule('email', ["email"]);
        $v->rule('lengthBetween', 'username', 4, 32);
        $v->rule('lengthBetween', 'password', 4, 32);
//        $v->rule('date', 'birth_date');

        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        if($this->getCollection()->count(['username'=> $entity['username']]) != 0){
            throw new ServiceException(ResponseHelper::error('Duplicate username'));
        }

        if(isset($params['picture'])){
            $entity['picture'] = Image::upload($params['picture'])->toArray();
        }

        $entity['password'] = md5($entity['password']);
        $entity['display_name'] = $entity['username'];

        $entity['display_notification_number'] = 0;
        $entity['type'] = 'admin';

        // register time
        $entity['created_at'] = new \MongoTimestamp();

        // set default last login
        $entity['last_login'] = new \MongoTimestamp(0);

        $this->getCollection()->insert($entity);

        //add stat helper
//        StatHelper::add('register', time(), 1);

//        MongoHelper::standardIdEntity($entity);
        unset($entity['password']);

        return $entity;
    }

    public function remove($id, Context $ctx){
        $id = MongoHelper::mongoId($id);

        $this->getCollection()->remove(array("_id"=> $id));

        return array("success"=> true);
    }
    
    public function event($user_id, Context $ctx) {
        
        $date = new \DateTime();
        $current_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_day = new \MongoDate($current_time);
        
        $start_time = strtotime($date->format('Y-m-d').' 00:00:00');
        $end_time = strtotime($date->format('Y-m-d').' 23:59:59');
        
        $items = $this->getSnifferCollection()->find([
            'user_id' => $user_id,
        ],['event_id']);
        
        $item_lists = [];
        foreach ($items as $item) {
            
            $event = $this->getEventCollection()->findOne([
                '_id' => new \MongoId($item['event_id']),
                '$or' => [
                    ['date_start' => ['$gte' => $current_day]],
                    [
                        '$and' => [
                            ['date_start' => ['$lte' => $current_day]],
                            ['date_end' => ['$gte' => $current_day]]
                        ]
                    ]
                ]
            ],['name','date_start','date_end','alarm']);
            
            if ($event !== null) {
                $event['id'] = $event['_id']->{'$id'};
                unset($event['_id']);

                $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
                $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);
                
                $picture = $this->getGalleryCollection()->findOne(['event_id' => $event['id']],['picture']);
                $event['picture'] = Image::load($picture['picture'])->toArrayResponse();

                $event['total_sniffer'] = $this->getSnifferCollection()->find(['event_id' => $event['id']])->count();
            
                $item_lists[] = $event;
            }
        }
        return $item_lists;
    }
    
    public function past($user_id, Context $ctx) {
        
        $date = new \DateTime();
        $current_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_day = new \MongoDate($current_time);
        
        $items = $this->getSnifferCollection()->find([
            'user_id' => $user_id,
        ],['event_id']);
        
        $item_lists = [];
        if ($items->count() > 0) {
            foreach($items as $item){
                
                $event = $this->getEventCollection()->findOne([
                    'approve' => 1,
                    'build' => 1,
                    '_id' => new \MongoId($item['event_id']),
                    'date_end' => ['$lt' => $current_day]
                ],['name','date_start','date_end']);
                
                if ($event !== null) {
                    $event['id'] = $event['_id']->{'$id'};
                    unset($event['_id']);

                    $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
                    $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);

                    $picture = $this->getGalleryCollection()->findOne(['event_id' => $event['id']],['picture']);
                    $event['picture'] = Image::load($picture['picture'])->toArrayResponse();

                    $event['total_sniffer'] = $this->getSnifferCollection()->find(['event_id' => $event['id']])->count();

                    $item_lists[] = $event;
                }
            }
            
            $item_lists = array_reverse($item_lists);
        }
        
        return $item_lists;
    }
    
//    public $prev = null;
    public function pictures($user_id, Context $ctx) {
        
        $date = new \DateTime();
        $current_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_day = new \MongoDate($current_time);
        
        $start_time = strtotime($date->format('Y-m-d').' 00:00:00');
        $end_time = strtotime($date->format('Y-m-d').' 23:59:59');
                
//        $ops = [
//            ['$match' => [
//                'user_id' => '54ba29c210f0edb8048b457a'
//            ]],
//            ['$group' => [
//                '_id' => ['id' => '$event_id']
//            ]]
//        ];
//        $g = $this->getGalleryCollection()->aggregate($ops);
//        dump($g['result']);
        
        $items = $this->getGalleryCollection()->find([
            'user_id' => $user_id
        ])->sort(['event_id' => -1]);
        
        $item_lists = [];
        foreach($items as $item){
            $item['picture'] = Image::load($item['picture'])->toArrayResponse();
            $item_lists[$item['event_id']][] = $item['picture'];
        }
        
        $item_pictures = [];
        foreach ($item_lists as $item => $value) {
            
            $event = $this->getEventCollection()->findOne([
                'approve' => 1,
                'build' => 1,
                '_id' => new \MongoId($item)
                ],['name','date_start','date_end']);
            
            if($event !== null){
                $event['id'] = $event['_id']->{'$id'};
                unset($event['_id']);
                
                $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
                $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);
                $event['picture_count'] = count($value);
                $event['pictures'] = $value;
                
                $item_pictures[] = $event;
            }
        }
        
        return $item_pictures;
    }
    
    public function update_profile_picture($user_id, $picture, Context $ctx) {
        $params = [
            'user_id' => $user_id,
            'picture' => $picture
        ];
        $v = new Validator($params);
        $v->rule('required', ["user_id", "picture"]);

        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $img = Image::upload($params['picture']);
        $set['picture'] = $img->toArray();
            
        $set = ArrayHelper::ArrayGetPath($set);
        $res = $this->getCollection()->update(['_id'=> new \MongoId($params['user_id'])], ['$set'=> $set]);
        if ($res['n'] == 0) {
            return false;
        }
        return true;
    }
    
    public function update_display_name($user_id, $display_name, Context $ctx) {
        $params = [
            'user_id' => $user_id,
            'display_name' => $display_name
        ];
        
        $v = new Validator($params);
        $v->rule('required', ["user_id", "display_name"]);

        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $res = $this->getCollection()->update(['_id'=> new \MongoId($params['user_id'])], ['$set'=> ['display_name' => $params['display_name']]]);
        if ($res['n'] == 0) {
            return false;
        }
        return true;
    }
    
    public function update_detail($user_id, $detail, Context $ctx) {
        $params = [
            'user_id' => $user_id,
            'detail' => $detail
        ];
        
        $v = new Validator($params);
        $v->rule('required', ["user_id", "detail"]);

        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $res = $this->getCollection()->update(['_id'=> new \MongoId($params['user_id'])], ['$set'=> ['detail' => $params['detail']]]);
        if ($res['n'] == 0) {
            return false;
        }
        return true;
    }
}