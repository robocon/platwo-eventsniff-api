<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/21/14
 * Time: 3:46 PM
 */

namespace Main\Service;


use Main\Context\Context,
    Main\DataModel\Image,
    Main\DB,
    Main\Exception\Service\ServiceException,
    Main\Helper\ArrayHelper,
    Main\Helper\MongoHelper,
    Main\Helper\ResponseHelper,
    Main\Helper\URL,
    Main\Helper\UserHelper,
    Main\Http\RequestInfo,
    Main\Helper\EventHelper,
    Valitron\Validator;

class UserService extends BaseService {
    protected $fields = ["type", "display_name", "username", "email", "password", "gender", "birth_date", "picture", "mobile", "website", "fb_id", "fb_name", "type", "detail"];

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
        $allow = ["username", "email", "password", "gender", "birth_date"];
        $data = ArrayHelper::filterKey($allow, $params);
        
        $v = new Validator($data);
        $v->rule('required', ["username", "email", "password"]);
        $v->rule('email', ["email"]);
        $v->rule('lengthBetween', 'username', 4, 32);
        $v->rule('lengthBetween', 'password', 6, 32);

        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        // Check username and email are duplicate?
        $user_count = $this->getCollection()->find([
            '$or' => [
                ['username' => $params['username']],
                ['email' => $params['email']]
            ]
        ])->count();
        if($user_count > 0){
            throw new ServiceException(ResponseHelper::validateError(['username'=> ['Duplicate username'], 'email'=> ['Duplicate email']]));
        }
        
        $now = new \MongoDate();
        $default_setting = UserHelper::defaultSetting();
        $birth_date = new \MongoDate(strtotime($data['birth_date'].' 00:00:00'));
        
        $id = new \MongoId();
        $user_private_key = UserHelper::generate_key();
        $access_token = UserHelper::generate_token(MongoHelper::standardId($id), $user_private_key);
        
        $notification_settings = UserHelper::notification_setting();
        
        $entity = [
            '_id' => $id,

            'password' => UserHelper::generate_password($data['password'], $user_private_key),
            'display_name' => $data['username'],
            'birth_date' => $birth_date,
            'email' => $data['email'],
            'username' => $data['username'],
            'gender' => $data['gender'],

            'display_notification_number' => 0,
            'type' => 'normal',

            'setting' => $default_setting,
            'notification' => $notification_settings,
            'sound' => $notification_settings,
            'created_at' => $now,
            'last_login' => $now,

            'access_token' => $access_token,
            'private_key' => $user_private_key,
            'level' => 1,
            'advertiser' => 0,

            'group_role' => ['group_id' => '54e855072667467f7709320e', 'role_perm_id' => '54eaf79810f0ed0d0a8b4568'],
            'picture' => [
                'id' => '54297c9390cc13a5048b4567png',
                'width' => 200,
                'height' => 200
            ]
        ];
        
        // If send from guest
        $token = RequestInfo::getToken();
        if($token === false){
            $this->getCollection()->insert($entity);
        }else{
            unset($entity['_id']);
            unset($entity['private_key']);
            unset($entity['created_at']);
            
            $token_count = $this->getCollection()->findOne(['access_token' => $token],['_id','private_key','access_token','type']);
            if ($token_count === null) {
                throw new ServiceException(ResponseHelper::error('Invalid token'));
            }
            
            // Override access_token
            $entity['access_token'] = UserHelper::generate_token($token_count['_id']->{'$id'}, $token_count['private_key']);
            $this->getCollection()->update(['_id' => new \MongoId($token_count['_id']->{'$id'})], ['$set' => $entity]);
            $entity['_id'] = $token_count['_id'];
        
        }
        
        // remember device token
        if(isset($params['ios_device_token'])){

            $hasToken = false;
            if(isset($entity['ios_device_token']) && is_array($entity['ios_device_token'])){
                foreach($entity['ios_device_token'] as $key => $value){
                    if($value['type'] == $params['ios_device_token']['type']
                        && $value['key'] == $params['ios_device_token']['key']){
                        $hasToken = true;
                    }
                }
            }
            if(!$hasToken){
                $this->getCollection()->update(['_id'=> $entity['_id']], ['$addToSet'=> ['ios_device_token'=> $params['ios_device_token'] ]]);
            }
            
        }
        if(isset($params['android_token'])){
            $this->getCollection()->update(['_id'=> $entity['_id']], ['$addToSet'=> ['android_token'=> $params['android_token'] ]]);
        }
        
        $res = [
            'user_id' => $entity['_id']->{'$id'},
            'access_token' => $entity['access_token'],
            'type' => $entity['type'],
        ];
        
        return $res;
    }
    
    public function noneuser($params, Context $ctx) {
        
//        $device_token = isset($params['ios_device_token']) ? $params['ios_device_token']['key'] : ( isset($params['android_token']) ? $params['android_token'] : null ) ; 
//        
//        if($device_token === null){
//            throw new ServiceException(ResponseHelper::error('Required device token'));
//        }
        
//        $user = $this->getCollection()->findOne([
//            '$or' => [
//                ['ios_device_token.key' => $device_token],
//                ['android_token' => $device_token]
//            ]
//        ]);
        
        $now = new \MongoDate();
        
//        if($user === null){
            
            $user_private_key = UserHelper::generate_key();
            
            $_id = new \MongoId();
            
            $notification_settings = UserHelper::notification_setting();
            
            $data = [
                '_id' => $_id,
                'display_name' => 'user '.uniqid(),
                'created_at' => $now,
                'last_login' => $now,
                'access_token' => UserHelper::generate_token(MongoHelper::standardId($_id), $user_private_key),
                'private_key' => $user_private_key,
                'notification' => $notification_settings,
                'type' => 'none',
                'level' => 0,
                'group_role' => ['group_id' => '54e855072667467f7709320e', 'role_perm_id' => '54eae7ab10f0ed0d0a8b4567']
            ];
            
            if(isset($params['ios_device_token'])){
                $data['ios_device_token'] = [$params['ios_device_token']];
            }
            
            if (isset($params['android_token'])) {
                $data['android_token'] = [$params['android_token']];
            }
            
            $this->getCollection()->insert($data);
            $res = [
                'user_id' => $data['_id']->{'$id'},
                'access_token' => $data['access_token'],
                'type' => $data['type'],
            ];
//        }else{
            
//            $data = [
//                'access_token' => UserHelper::generate_token($user['_id']->{'$id'}, $user['private_key']),
//                'last_login' => $now,
//            ];
//            
//            $this->getCollection()->update(['_id' => $user['_id']], ['$set' => $data]);
//            $res = [
//                'user_id' => $user['_id']->{'$id'},
//                'access_token' => $user['access_token'],
//                'type' => $user['type'],
//            ];
//        }
        
        return $res;
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
        
        if(!empty($entity['birth_date'])){
            $entity['birth_date'] = MongoHelper::dateToYmd($entity['birth_date']);
        }
        
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
    
    public function event( Context $ctx ) {
        
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        $read_user = isset($_GET['user_id']) ? trim($_GET['user_id']) : false ;
        if( $read_user !== false ){
            $db = DB::getDB();
            $user = $db->users->findOne([ '_id' => new \MongoId($read_user) ]);
            if( $user === null ){
                throw new ServiceException(ResponseHelper::error('Invalid user'));
            }
        }
        
        $user_id = $user['_id']->{'$id'};
        $user_category = isset($user['sniff_category']) ? $user['sniff_category'] : [] ;
        
        $date = new \DateTime();
        $current_time = $date->getTimestamp();
        $current_day = new \MongoDate($current_time);
        
//        $start_time = strtotime($date->format('Y-m-d').' 00:00:00');
//        $end_time = strtotime($date->format('Y-m-d').' 23:59:59');
        
//        $items = $this->getSnifferCollection()->find([
//            'user_id' => $user_id,
//        ],['event_id']);
        
        $items = $this->getEventCollection()->find([
//            '_id' => new \MongoId($item['event_id']),
//            'sniffer' => $user_id,
            '$and' => [
                ['$or' => [
                    ['categories' => ['$in' => $user_category] ],
                    ['sniffer' => $user_id]
                ]],
                ['$or' => [
                    ['date_start' => ['$gte' => $current_day]],
                    [
                        '$and' => [
                            ['date_start' => ['$lte' => $current_day]],
                            ['date_end' => ['$gte' => $current_day]]
                        ]
                    ]
                ]]
            ]
        ],['name','date_start','date_end','alarm','sniffer']);
        
        $item_lists = [];
        foreach ($items as $event) {
//            dump($event);
//            exit;
            
//            if ($event !== null) {
            $event['id'] = $event['_id']->{'$id'};
            unset($event['_id']);

            $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
            $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);

            $pic = $this->getGalleryCollection()->findOne(['event_id' => $event['id']],['picture']);
            $pic['id'] = $pic['_id']->{'$id'};
            unset($pic['_id']);
            $pic['picture'] = Image::load_picture($pic['picture']);
            $pic['detail'] = isset($pic['detail']) ? $pic['detail'] : '' ;
            $event['thumb'] = $pic;
            
            $event['sniffed'] = 'false';
            if(in_array($user_id, $event['sniffer']) ){
                $event['sniffed'] = 'true';
            }
            
            $event['total_sniffer'] = count($event['sniffer']);
            unset($event['sniffer']);

            if($event['alarm'] != 0 && count($event['alarm']) > 0){
                $test_alarm = false;
                foreach($event['alarm'] as $alarm){
                    if($user_id == $alarm['user_id']){
                        $event['alarm'] = $alarm;
                        $test_alarm = true;
                    }
                }

                if($test_alarm === false){
                    $event['alarm'] =  new \stdClass();
                }

            }else{
                $event['alarm'] =  new \stdClass();
            }

            $item_lists[] = $event;
//            }
        }
        
        return $item_lists;
    }
    
    public function past( Context $ctx ) {
        
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        $read_user = isset($_GET['user_id']) ? trim($_GET['user_id']) : false ;
        if( $read_user !== false ){
            $db = DB::getDB();
            $user = $db->users->findOne([ '_id' => new \MongoId($read_user) ]);
            if( $user === null ){
                throw new ServiceException(ResponseHelper::error('Invalid user'));
            }
        }
        
        $user_id = $user['_id']->{'$id'};
        $user_category = isset($user['sniff_category']) ? $user['sniff_category'] : [] ;
        
        $date = new \DateTime();
        $current_time = $date->getTimestamp();
        $current_day = new \MongoDate($current_time);
        
        $items = $this->getEventCollection()->find([
            'approve' => 1,
            'build' => 1,
            '$and' => [
                ['date_end' => ['$lt' => $current_day]],
                ['$or' => [
                    ['categories' => ['$in' => $user_category] ],
                    ['sniffer' => $user_id]
                ]]
            ],
            
        ],['name','date_start','date_end','sniffer','user_id','check_in'])->limit(10);
        
        $item_lists = [];
        if ($items->count() > 0) {
            foreach($items as $event){
                
                $event['id'] = $event['_id']->{'$id'};
                unset($event['_id']);
                
                $event['owner'] = EventHelper::get_owner($event['user_id']);

                $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
                $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);

                $event['pictures'] = EventHelper::get_gallery($event['id']);

                $event['total_sniffer'] = count($event['sniffer']);
                
                $comment = EventHelper::get_comments( $event['id'] );
                $event['total_comment'] = $comment['count'];
                
                $event['total_checkin'] = 0;
                if(isset($event['check_in'])){
                    $event['total_checkin'] = count($event['check_in']);
                }
                
                unset($event['sniffer']);
                
                $event['node'] = [ "share"=> URL::share('index.php?page=share&id='.$event['id']) ];
                
                $item_lists[] = $event;
            }
            $item_lists = array_reverse($item_lists);
        }
        return $item_lists;
    }
    
//    public $prev = null;
    public function pictures( Context $ctx ) {
        
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        $read_user = isset($_GET['user_id']) ? trim($_GET['user_id']) : false ;
        if( $read_user !== false ){
            $db = DB::getDB();
            $user = $db->users->findOne([ '_id' => new \MongoId($read_user) ]);
            if( $user === null ){
                throw new ServiceException(ResponseHelper::error('Invalid user'));
            }
        }
        
        $user_id = $user['_id']->{'$id'};

//        $date = new \DateTime();
//        $current_time = $date->getTimestamp();
//        $current_day = new \MongoDate($current_time);
//        
//        $start_time = strtotime($date->format('Y-m-d').' 00:00:00');
//        $end_time = strtotime($date->format('Y-m-d').' 23:59:59');
        
        $items = $this->getEventCollection()->find([
            'approve' => 1,
            'build' => 1,
            'check_in' => $user_id
        ],['name','date_start','date_end','user_id'])
        ->sort(['date_start' => -1])
        ->limit(10);
        
        $db = DB::getDB();
        $item_pictures = [];
        foreach ($items as $key => $item) {
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
            $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
            $pictures = $db->gallery->find([
                'event_id' => $item['id'], 
                'user_id' => $user_id 
                ],['picture','user_id','detail']);
            
            $picture_lists = [];
            $pic_count = 0;
            foreach($pictures as $pic){

                $pic['id'] = $pic['_id']->{'$id'};
                unset($pic['_id']);
                $pic['picture'] = Image::load_picture($pic['picture']);
                $pic['detail'] = isset($item['detail']) ? $item['detail'] : '' ;
                
                $pic_user = $db->users->findOne(['_id' => new \MongoId($pic['user_id']) ],['display_name']);
                $pic_user['id'] = $pic_user['_id']->{'$id'};
                unset($pic_user['_id']);
                $pic['user'] = $pic_user;
                unset($pic['user_id']);
                
                $picture_lists[] = $pic;
                $pic_count++;
            }
            $item['pictures'] = $picture_lists;
            $item['total_picture'] = $pic_count;
            
            $item['owner'] = EventHelper::get_owner($item['user_id']);
            unset($item['user_id']);
            
            $item_pictures[] = $item;
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
            return ['success' => false];
        }
        $img_res['picture'] = Image::load($set['picture']);
        $img_res['success'] = true;
        return $img_res;
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
    
    public function update_gender($user_id, $gender, Context $ctx){
        $params = [
            'user_id' => $user_id,
            'gender' => $gender
        ];
        
        $v = new Validator($params);
        $v->rule('required', ["user_id", "gender"]);

        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $res = $this->getCollection()->update(['_id'=> new \MongoId($params['user_id'])], ['$set'=> ['gender' => $params['gender']]]);
        if ($res['n'] == 0) {
            return false;
        }
        return true;
    }
    
    public function update_birth_date($user_id, $birth_date, Context $ctx){
        
        $birth_date = new \MongoDate(strtotime($birth_date." 00:00:00"));
        $params = [
            'user_id' => $user_id,
            'birth_date' =>  $birth_date
        ];
        
        $v = new Validator($params);
        $v->rule('required', ["user_id", "birth_date"]);

        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $res = $this->getCollection()->update(['_id'=> new \MongoId($params['user_id'])], ['$set'=> ['birth_date' => $params['birth_date']]]);
        
        if ($res['n'] == 0) {
            return false;
        }
        return true;
    }
    
    public function update_username($user_id, $username, Context $ctx) {

        $params = [
            'user_id' => $user_id,
            'username' =>  $username
        ];
        
        $v = new Validator($params);
        $v->rule('required', ["user_id", "username"]);
        $v->rule('alphaNum', ["username"]);

        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $check_username = $this->getCollection()->findOne([
            'username' => $username,
            '_id' => [ '$ne' => new \MongoId($user_id) ]
        ],['_id']);
        
        if ($check_username !== null) {
            throw new ServiceException(ResponseHelper::error('Username already exist'));
        }
        
        $res = $this->getCollection()->update(['_id'=> new \MongoId($params['user_id'])], ['$set'=> ['username' => $params['username']]]);
        
        if ($res['n'] == 0) {
            return false;
        }
        return true;
    }
    
    public function update_email($user_id, $email, Context $ctx) {
        
        $params = [
            'user_id' => $user_id,
            'email' =>  $email
        ];
        
        $v = new Validator($params);
        $v->rule('required', ["user_id", "email"]);
        $v->rule('email', ["email"]);

        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $check_email = $this->getCollection()->findOne([
            'email' => $email,
            '_id' => [ '$ne' => new \MongoId($user_id) ]
        ],['_id']);
        
        if ($check_email !== null) {
            throw new ServiceException(ResponseHelper::error('Email already exist'));
        }
        
        $res = $this->getCollection()->update(['_id'=> new \MongoId($params['user_id'])], ['$set'=> ['email' => $params['email']]]);
        
        if ($res['n'] == 0) {
            return false;
        }
        return true;
    }
    
    public function update_password($user_id, $params, Context $ctx) {
        
        $data = [
            'user_id' => $user_id,
            'password' =>  $params['password'],
            'new_password' =>  $params['new_password'],
            'confirm_password' =>  $params['confirm_password'],
        ];
        
        $v = new Validator($data);
        $rules = [
            'lengthMin' => [
                ['password', 6],
                ['new_password', 6],
                ['confirm_password', 6]
            ]
        ];
        $v->rules($rules);
        $v->rule('equals', 'new_password', 'confirm_password');

        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $user = $this->getCollection()->findOne([
            '_id' => new \MongoId($user_id)
        ],['private_key', 'password']);
        if ($user === null) {
            throw new ServiceException(ResponseHelper::error('Invalid user'));
        }
        
        // Check old password was match from database?
        if(isset($user['password'])){
            $old_password = UserHelper::generate_password($data['password'], $user['private_key']);
            if($old_password !== $user['password']){
                throw new ServiceException(ResponseHelper::error('Invalid password'));
            }
        }
        
        $password = UserHelper::generate_password($data['new_password'], $user['private_key']);
        $res = $this->getCollection()->update(['_id'=> new \MongoId($user_id)], ['$set'=> ['password' => $password]]);
        if ($res['n'] == 0) {
            return false;
        }
        return true;
    }
    
    public function update_location($user_id, $params, Context $ctx) {
        
        $data = [
            'user_id' => $user_id,
            'country' =>  $params['country'],
            'city' =>  $params['city'],
        ];
        
        $v = new Validator($data);
        $v->rule('required', ["user_id", "country", "city"]);

        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $check_user = $this->getCollection()->findOne([
            '_id' => new \MongoId($data['user_id'])
        ],['_id']);

        if ($check_user === null) {
            throw new ServiceException(ResponseHelper::error('Invalid user'));
        }
        
        $res = $this->getCollection()->update(['_id' => new \MongoId($data['user_id'])], ['$set'=> ['default_location' => [$data['country'], $data['city']]]]);
        if ($res['n'] == 0) {
            return false;
        }
        return true;
    }
    
    public function update_website($user_id, $website, Context $ctx) {
        
        $params = [
            'user_id' => $user_id,
            'website' => $website,
        ];
        
        $v = new Validator($params);
        $v->rule('required', ["user_id", "website"]);
        $v->rule('url', 'website');
        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $check_user = $this->getCollection()->findOne([
            '_id' => new \MongoId($params['user_id'])
        ],['_id']);

        if ($check_user === null) {
            throw new ServiceException(ResponseHelper::error('Invalid user'));
        }
        
        $res = $this->getCollection()->update(['_id'=> new \MongoId($params['user_id'])], ['$set'=> ['website' => $params['website']]]);
        if ($res['n'] == 0) {
            return false;
        }
        return true;
    }
    
    public function update_phone($user_id, $phone, Context $ctx) {
        
        $params = [
            'user_id' => $user_id,
            'phone' => $phone,
        ];
        
        $v = new Validator($params);
        $v->rule('required', ["user_id", "phone"]);
        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $check_user = $this->getCollection()->findOne([
            '_id' => new \MongoId($params['user_id'])
        ],['_id']);

        if ($check_user === null) {
            throw new ServiceException(ResponseHelper::error('Invalid user'));
        }
        
        $res = $this->getCollection()->update(['_id'=> new \MongoId($params['user_id'])], ['$set'=> ['mobile' => $params['phone']]]);
        if ($res['n'] == 0) {
            return false;
        }
        return true;
    }
    
    public function update_facebook_name($user_id, $fb_name, Context $ctx) {
        
        $params = [
            'user_id' => $user_id,
            'fb_name' => $fb_name,
        ];
        
        $v = new Validator($params);
        $v->rule('required', ["user_id", "fb_name"]);
        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $check_user = $this->getCollection()->findOne([
            '_id' => new \MongoId($params['user_id'])
        ],['_id']);

        if ($check_user === null) {
            throw new ServiceException(ResponseHelper::error('Invalid user'));
        }
        
        $res = $this->getCollection()->update(['_id'=> new \MongoId($params['user_id'])], ['$set'=> ['fb_name' => $params['fb_name']]]);
        if ($res['n'] == 0) {
            return false;
        }
        return true;
    }
    
    public function replace_picture(Context $ctx){
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        // Using Default picture
        $update_picture['picture'] = [
            'id'=> '54297c9390cc13a5048b4567png',
            'width'=> 200,
            'height'=> 200
        ];
        
        $res = $this->getCollection()->update(['_id'=> $user['_id']],['$set' => $update_picture]);
        if ($res['n'] == 0) {
            $update_picture['success'] = false;
            unset($update_picture['picture']);
        }
        
        $update_picture['success'] = true;
        return $update_picture;
    }
    
    public function update_notify($params, Context $ctx){
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        $v = new Validator($params);
        $v->rule('required', ["type", "status"]);
        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $status = ($params['status'] == 'true') ? 'true' : 'false' ;
        
        if($params['type'] == 'alarm'){
            $update = ['notification.alarm' => $status];
            
        } else if($params['type'] == 'event'){
            $update = ['notification.event_update' => $status];
            
        } else if($params['type'] == 'category'){
            $update = ['notification.category_add_event' => $status];
            
        } else if($params['type'] == 'checkin'){
            $update = ['notification.like_checkin' => $status];
            
        } else {
            throw new ServiceException(ResponseHelper::error('Invalid type'));
        }
        
        $res = $this->getCollection()->update(['_id'=> $user['_id']],['$set' => $update]);
        if ($res['n'] == 0) {
            return false;
        }
        return true;
        
    }
    
    public function get_notify(Context $ctx){
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        $item = $this->getCollection()->findOne(['_id' => $user['_id']],['notification']);
        return [
            'alarm' => $item['notification']['alarm'],
            'event' => $item['notification']['event_update'],
            'category' => $item['notification']['category_add_event'],
            'checkin' => $item['notification']['like_checkin']
        ];
    }
    
    public function update_sound($params, Context $ctx) {
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        $v = new Validator($params);
        $v->rule('required', ["type", "status"]);
        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $status = ($params['status'] == 'true') ? 'true' : 'false' ;
        
        if($params['type'] == 'alarm'){
            $update = ['sound.alarm' => $status];
            
        } else if($params['type'] == 'event'){
            $update = ['sound.event_update' => $status];
            
        } else if($params['type'] == 'category'){
            $update = ['sound.category_add_event' => $status];
            
        } else if($params['type'] == 'checkin'){
            $update = ['sound.like_checkin' => $status];
            
        } else {
            throw new ServiceException(ResponseHelper::error('Invalid type'));
        }
        
        $res = $this->getCollection()->update(['_id'=> $user['_id']],['$set' => $update]);
        if ($res['n'] == 0) {
            return false;
        }
        return true;
        
    }
    
    public function get_sound(Context $ctx){
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        $item = $this->getCollection()->findOne(['_id' => $user['_id']],['sound']);
        return [
            'alarm' => $item['sound']['alarm'],
            'event' => $item['sound']['event_update'],
            'category' => $item['sound']['category_add_event'],
            'checkin' => $item['sound']['like_checkin']
        ];
    }
    
    public function set_sniff_around($params, Context $ctx){
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        if(!isset($params['cities'])){
            $params['cities'] = [];
        }
        
        $this->getCollection()->update(
            ['_id' => $user['_id']],
            ['$set' => [ 'sniffing_around' => $params['cities'] ]]
        );
        return ['success' => 'true', 'cities' => $params['cities']];
    }
    
    public function get_sniff_around(Context $ctx){
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        $db = DB::getDB();
        $item = $this->getCollection()->findOne(['_id' => $user['_id']],['sniffing_around']);
        $cities = [];
        foreach($item['sniffing_around'] as $around){
            $city = $db->cities->findOne(['_id' => new \MongoId($around)],['name']);
            $cities[] = [
                'id' => $around,
                'name' => $city['name']
            ];
        }
        
        return ['length' => count($cities), 'cities' => $cities];
    }
}