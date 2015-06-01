<?php
/**
 * Created by PhpStorm.
 * User: robocon
 * Date: 1/10/15
 * Time: 10:27 AM
 */

namespace Main\Service;

use Main\Context\Context,
    Main\DB,
    Main\DataModel\Image,
    Main\Exception\Service\ServiceException,
    Main\Helper\ArrayHelper,
    Main\Helper\MongoHelper,
    Main\Helper\ResponseHelper,
    Main\Helper\UserHelper,
    Main\Helper\URL,
    Main\Helper\NotifyHelper,
    Valitron\Validator,
    Main\Http\RequestInfo;

class EventService extends BaseService {

    /**
     * Collection event
     */
    public function getCollection(){
        $db = DB::getDB();
        return $db->event;
    }

    /**
     * Collection gallery
     */
    public function getGalleryCollection(){
         $db = DB::getDB();
        return $db->gallery;
    }

    /**
     * Collection sniffer
     */
    public function getSnifferCollection(){
         $db = DB::getDB();
        return $db->sniffer;
    }

    /**
     * Collection comment
     */
    public function getCommentCollection(){
         $db = DB::getDB();
        return $db->comment;
    }

    /**
     * Collection users
     */
    public function getUsersCollection(){
         $db = DB::getDB();
        return $db->users;
    }

    /**
     * Collection tag
     */
    public function getTagCollection(){
         $db = DB::getDB();
        return $db->tag;
    }

    /**
     * Collection event_tag
     */
    public function getEventTagCollection(){
         $db = DB::getDB();
        return $db->event_tag;
    }
    
    /**
     * Collection location
     */
    public function getLocationCollection(){
         $db = DB::getDB();
        return $db->location;
    }
    
    public function all(Context $ctx) {
        
        $params = [
            'country' => RequestInfo::getHeader('country'),
            'city' => RequestInfo::getHeader('city')
        ];
        
        $date = new \DateTime();
        $current_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_day = new \MongoDate($current_time);
        
        $condition = [
            'approve' => 1,
            'build' => 1,
            '$or' => [
                ['date_start' => ['$gte' => $current_day]],
                [
                    '$and' => [
                        ['date_start' => ['$lte' => $current_day]],
                        ['date_end' => ['$gte' => $current_day]]
                    ]
                ]
            ]
        ];
        if($params['country'] !== false && $params['city'] !== false){
            $condition = [
                'approve' => 1,
                'build' => 1,
                '$and' => [
                    ['country' => $params['country']],
                    ['city' => $params['city']],
                    [
                        '$or' => [
                            ['date_start' => ['$gte' => $current_day]],
                            [
                                '$and' => [
                                    ['date_start' => ['$lte' => $current_day]],
                                    ['date_end' => ['$gte' => $current_day]]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }
        
        $start_time = strtotime($date->format('Y-m-d').' 00:00:00');
        $end_time = strtotime($date->format('Y-m-d').' 23:59:59');
        
        $events = $this->getCollection()->find($condition,['name', 'date_start', 'date_end'])->sort(['date_start' => 1]);
        
        $group_one = [];
        $group_two =[];
        
        foreach ($events as $item) {
            $item_start = $item['date_start']->{'sec'};
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $item['group_date'] = date('Y-m-d', $item['date_start']->{'sec'});
            $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
            $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
            
            $picture = $this->getGalleryCollection()->findOne(['event_id' => $item['id']],['picture']);
            $item['thumb'] = Image::load($picture['picture'])->toArrayResponse();
                
            $item['total_sniffer'] = $this->getSnifferCollection()->find(['event_id' => $item['id']])->count();
            
            if ($item_start > $start_time && $item_start < $end_time) {
                $group_one[] = $item;
            }else{
                $group_two[] = $item;
            }
        }
        
        return array_merge($group_one, $group_two);
    }
    
    public function gets($options = [], Context $ctx) {

        $default = array(
            "page"=> 1,
            "limit"=> 15
        );
        $options = array_merge($default, $options);
        $skip = ($options['page']-1) * $options['limit'];

        $items = $this->getCollection()
                ->find(['build' => 1, 'approve' => 1])
                ->limit($options['limit'])
                ->skip($skip);
        $length = $items->count(true);
        $total = $this->getCollection()->count(['build' => 1, 'approve' => 1]);

        $data = [];
        foreach ($items as $item) {

            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);

            // Get last Picture
            $picture = $this->getGalleryCollection()
                    ->find(['event_id' => $item['id']])
                    ->sort(['_id' => -1]) // Look like DESC in MySQL
                    ->limit(1);

            if ($picture->count(true)) {
                foreach($picture as $pic){
                    $item['thumb'] = Image::load($pic['picture'])->toArrayResponse();
                }
            }

            $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
            $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
            $item['time_edit'] = MongoHelper::dateToYmd($item['time_edit']);
            $item['time_stamp'] = MongoHelper::dateToYmd($item['time_stamp']);
            
            unset($item['build']);
            unset($item['approve']);
            
            $data[] = $item;
        }

        $res = [
            'length' => $length,
            'total' => $total,
            'data' => $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        ];

        if ($length > 0 && $length <= $total ) {
            $res['paging']['next'] = URL::absolute('/event'.'?'.  http_build_query(['page' => (int)$options['page']+1, 'limit' => (int)$options['limit']]));

            if ($options['page'] > 1) {
                $res['paging']['prev'] = URL::absolute('/event'.'?'.  http_build_query(['page' => (int)$options['page']-1, 'limit' => (int)$options['limit']]));
            }
        }
        
        return $res;
    }

    public function get($id, Context $ctx) {

        $id = MongoHelper::mongoId($id);
        $item = $this->getCollection()->findOne(['_id' => $id],['_id','name','detail','credit','alarm','date_end','date_start','time_edit','time_stamp']);

        $item['id'] = $item['_id']->{'$id'};
        unset($item['_id']);

        $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
        $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
        $item['time_edit'] = MongoHelper::dateToYmd($item['time_edit']);
        $item['time_stamp'] = MongoHelper::dateToYmd($item['time_stamp']);
        
        $token = RequestInfo::getHeader('access-token');
        if($token !== false && !empty($item['alarm'])){
            
            // Find own alarm by access-token
            $user = $this->getUsersCollection()->findOne(['access_token' => $token],['_id']);
            if($user === null){
                return ResponseHelper::error('Invalid token');
            }
            
            $test_alarm = false;
            foreach($item['alarm'] as $alarm){
                if($user['_id']->{'$id'} == $alarm['user_id']){
                    $item['alarm'] = $alarm;
                    $test_alarm = true;
                }
            }
            
            if($test_alarm === false){
                $item['alarm'] = [];
            }
            
        }else{
            
            // If not access-token return empty array
            $item['alarm'] = [];
        }
            
        // Get location
        $location = $this->getLocationCollection()->findOne([
            'event_id' => $item['id']
        ],['name','position']);
        $location['id'] = $location['_id']->{'$id'};
        unset($location['_id']);
        
        if (!is_array($location['position'])) {
            $position = explode(',', $location['position']);
            $location['position'] = array_map('trim',$position);
        }
        $item['location'] = $location;
        
        // Get latest 5 pictures
        $gallery = $this->getGalleryCollection()
            ->find(['event_id' => $item['id']],['picture','detail'])
            ->sort(['_id' => -1])
            ->limit(5);
        $item['pictures'] = [];

        if ($gallery->count(true)) {
            $pictures = [];
            foreach ($gallery as $picture) {
                
                $set_url = Image::load($picture['picture'])->toArrayResponse();
                
                if(isset($picture['detail'])){
                    $set_url['detail'] = $picture['detail'];
                }
                
                $pictures[] = $set_url;
            }
            $item['pictures'] = $pictures;
        }

        // Get latest 20 sniffer
        $sniffers = $this->getSnifferCollection()
            ->find(['event_id' => $item['id']])
            ->sort(['_id' => -1])
            ->limit(20);
        $item['total_sniffer'] = $this->getSnifferCollection()->find(['event_id' => $item['id']],['_id','user_id'])->count();
        $item['sniffer'] = [];
        if ($sniffers->count(true)) {
            $user_lists = [];
            foreach($sniffers as $sniffer){
                $sniffer['id'] = $sniffer['_id']->{'$id'};
                unset($sniffer['_id']);

                // Get user detail
                $user = $this->getUsersCollection()->findOne(array("_id" => MongoHelper::mongoId($sniffer['user_id'])),['_id','display_name','picture']);
                $user_lists[] = [
                    'id' => $user['_id']->{'$id'},
                    'name' => $user['display_name'],
                    'picture' => Image::load($user['picture'])->toArrayResponse()
                ];
            }
            $item['sniffer'] = $user_lists;
        }

        // get latest 3 comment
        $comment_lists = $this->getCommentCollection()
            ->find(['event_id' => $item['id']],['_id','detail','user_id','time_stamp'])
            ->sort(['_id' => -1])
            ->limit(3);
        $item['total_comment'] = $this->getCommentCollection()->find(['event_id' => $item['id']])->count();
        $item['comments'] = [];
        if ($comment_lists->count(true)) {
            $comments = [];

            foreach($comment_lists as $comment){
                $comment['id'] = $comment['_id']->{'$id'};
                unset($comment['_id']);

                // Get user detail
                $user = $this->getUsersCollection()->findOne(array("_id" => MongoHelper::mongoId($comment['user_id'])),['display_name','picture']);
                $comment['user'] = [
                    'display_name' => $user['display_name'],
                    'picture' => Image::load($user['picture'])->toArrayResponse()
                ];
                $comment['time_stamp'] = MongoHelper::timeToStr($comment['time_stamp']);

                $comments[] = $comment;
            }
            $item['comments'] = $comments;
        }
        $item['node'] = [ "share"=> URL::share('/event.php?id='.$item['id']) ];
        
        return $item;
    }

    /**
     * Add from website
     *
     * @param type $params
     * @param Context $ctx
     * @return type
     * @throws ServiceException
     */
    public function add($params, Context $ctx){


        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'date_start', 'date_end', 'approve', 'thumb', 'credit', 'user_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $data = [
            'name' => $params['name'],
            'detail' => $params['detail'],
            'date_start' => $params['date_start'],
            'date_end' => $params['date_end'],
            'approve' => $params['approve'],
            'thumb' => $params['thumb'],
            'credit' => $params['credit'],
            'user_id' => $params['user_id'],
            'time_stamp' => $params['time_stamp'],
            'time_edit' => $params['time_stamp']
        ];

        $insert = ArrayHelper::filterKey(['name', 'detail', 'date_start', 'date_end', 'approve', 'thumb', 'credit', 'user_id', 'time_stamp', 'time_edit'], $data);
        $this->getCollection()->insert($insert);

        return $insert;
    }

    public function updateThumb($id, $picture, Context $ctx){

        // Set this ID First
        $id = MongoHelper::mongoId($id);
        $set['thumb'] = $picture;

        $this->getCollection()->update(['_id'=> $id], ['$set'=> $set]);
    }

    /**
     * Add event from mobile
     *
     * @param array $params
     * @param Context $ctx
     * @return type
     * @throws ServiceException
     */
    public function mobile_add($params, Context $ctx) {

        $v = new Validator($params);
        $v->rule('required', ['user_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        // Set build and approve to 0 if send from mobile
        $params['build'] = 0;
        $params['approve'] = 0;
        $params['time_stamp'] = new \MongoDate();
        $params['alarm'] = 0;
        $params['advertise'] = [
            'status' => 0
        ];
        
        $insert = ArrayHelper::filterKey(['user_id', 'build', 'approve', 'time_stamp', 'alarm', 'advertise'], $params);

        $this->getCollection()->insert($insert);
        return $insert;
    }

    public function edit($id, $params, Context $ctx) {

        // Set this ID First
        $id = MongoHelper::mongoId($id);

        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'date_start', 'date_end', 'credit', 'country', 'city']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $set['name'] = $params['name'];
        $set['detail'] = $params['detail'];
        $set['date_start'] = new \MongoDate(strtotime($params['date_start']));
        $set['date_end'] = new \MongoDate(strtotime($params['date_end']));
        $set['credit'] = $params['credit'];
        $set['time_edit'] = new \MongoDate(time());
        $set['build'] = 1;
        $set['country'] = $params['country'];
        $set['city'] = $params['city'];
        $set['alarm'] = [];

        $this->getCollection()->update(['_id'=> $id], ['$set'=> $set]);
        unset($set['build']);
//        $set['local'] = $set['location'];
//        unset($set['location']);
        return $set;
    }

    public function alarm($params, Context $ctx) {

        $v = new Validator($params);
        $v->rules([
                'required' => [ ['event_id'], ['active'], ['user_id'] ],
                'integer' => [ ['active'] ],
                'length' => [['active', 1]]
            ]);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        // Set this ID First
        $event_id = MongoHelper::mongoId($params['event_id']);
        
        $find_event = $this->getCollection()->findOne([
            '_id' => $event_id,
            'alarm.user_id' => $params['user_id']
        ]);
        
        $data = [
            'user_id' => $params['user_id'],
            'active' => $params['active'],
            'alarm_date' => new \MongoDate(strtotime($params['alarm_date']))
        ];
        
        // Check alarm is array
        $ev = $this->getCollection()->findOne(['_id' => $event_id],['alarm']);
        if($ev['alarm'] == 0){
            $update = $this->getCollection()->update(
                ['_id' => $event_id],
                ['$set' => ['alarm' => []]]
            );
        }
        
        if($find_event === null){
            $update = $this->getCollection()->update(
                ['_id' => $event_id],
                ['$addToSet' => ['alarm' => $data]]
            );
        }else{
            
            $update = $this->getCollection()->update(
                ['_id' => $event_id, 'alarm.user_id' => $params['user_id']],
                ['$pull' => ['alarm' => ['user_id' => $params['user_id']]]]
            );
            
            $update = $this->getCollection()->update(
                ['_id' => $event_id],
                ['$addToSet' => ['alarm' => $data]]
            );
        }
        $data['event_id'] = $params['event_id'];

        return $data;
    }
    
    public function notify_alarm($params, Context $ctx){
        
        $user = $ctx->getUser();
        if($user === null){
            ResponseHelper::error('Access denied');
        }
        
        $v = new Validator($params);
        $v->rule('required', ['event_id']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $item = $this->getCollection()->findOne([
            '_id' => new \MongoId($params['event_id']),
//            'alarm' => [ '$ne' => 0 ]
        ],['name','alarm']);
        
        $message = 'Alarm for '.$item['name'];
        
        $insert = ArrayHelper::filterKey(['message'], [ 'message' => $message ]);
        MongoHelper::setCreatedAt($insert);

        DB::getDB()->messages->insert($insert);
        
//        $user_id = new \MongoId($item['user_id']);
//        $user = $this->getUsersCollection()->findOne(['_id' => $user_id]);

        $entity = NotifyHelper::create($insert['_id'], "alarm", "ข้อความจากระบบ", $message, $user['_id']->{'$id'}, $item['_id']);
        NotifyHelper::incBadge($user['_id']->{'$id'});
        $user['display_notification_number']++;

        $args = [
            'id'=> MongoHelper::standardId($entity['_id']),
            'object_id'=> MongoHelper::standardId($insert['_id']),
            'type'=> "alarm"
        ];

//        if(!isset($user['setting']))
//            continue;

        if(!$user['setting']['notify_message']){
            ResponseHelper::error('Notification message not enable');
        }
//            continue;

        NotifyHelper::send($user, $entity['preview_content'], $args);
        
//        foreach($items['alarm'] as $key => $item){
//
//            
//        }
        
        $res = [
            'user_id' => $user['_id']->{'$id'},
            'message' => $message,
        ];
        return $res;
//        exit;
    }

    public function category_lists($category_lists, Context $ctx) {
        
        $params = [
            'country' => RequestInfo::getHeader('country'),
            'city' => RequestInfo::getHeader('city')
        ];
        
        $date = new \DateTime();
        $set_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_time = new \MongoDate($set_time);

        $new_category_lists = [];
        foreach($category_lists as $category){

            $event_tags = $this->getEventTagCollection()->find(['tag_id' => $category['id']]);
            
            // Count an event from event_tag
            $event_tags_count = $event_tags->count(true);
            
            if ($event_tags_count > 0) {
                
                $event_lists = [];
                $i = 0;
                foreach ($event_tags as $item) {
                    
                    $condition = [
                        'approve' => 1,
                        'build' => 1,
                        '_id' => new \MongoId($item['event_id']),
                        'date_start' => ['$gt' => $current_time]
                    ];
                    
                    if($params['country'] !== false && $params['city'] !== false){
                        $condition = [
                            'approve' => 1,
                            'build' => 1,
                            'country' => $params['country'],
                            'city' => $params['city'],
                            '_id' => new \MongoId($item['event_id']),
                            'date_start' => ['$gt' => $current_time]
                        ];
                    }
                    
                    $event = $this->getCollection()->findOne($condition,['name', 'date_start', 'date_end']);
                    
                    if ($event !== null) {
                        $event['id'] = $event['_id']->{'$id'};
                        unset($event['_id']);
                        
                        $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
                        $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);
                        
                        $set_key = (string)strtotime($event['date_start']);
                        
                        $picture = $this->getGalleryCollection()->findOne(['event_id' => $event['id']],['picture']);
                        $event['thumb'] = Image::load($picture['picture'])->toArrayResponse();
                        
                        $event_lists[$set_key] = $event;
                        $i++;
                    }
                }
                
                if ($i > 0) {
                    ksort($event_lists, SORT_NUMERIC);
                    $get_keys = array_keys($event_lists);
                    $first_event = $get_keys['0'];
                    $real_event = $event_lists[$first_event];
                    
                    $category['thumb'] = $real_event['thumb'];
                    
                    $new_category_lists[] = $category;
                }
            }
        }
        
        return $new_category_lists;
    }
    
    public function today($lang, Context $ctx) {
        
        $params = [
            'country' => RequestInfo::getHeader('country'),
            'city' => RequestInfo::getHeader('city')
        ];
        
        $date = new \DateTime();
        $current_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_day = new \MongoDate($current_time);
        
        $start_time = strtotime($date->format('Y-m-d').' 00:00:00');
        $end_time = strtotime($date->format('Y-m-d').' 23:59:59');
        
//        $new_category_lists = [];
//        $first_category_lists = [];
        
        $condition = [
            'approve' => 1,
            'build' => 1,
            'date_start' => ['$lte' => $current_day],
            'date_end' => ['$gte' => $current_day]
        ];
        
        if($params['country'] !== false && $params['city'] !== false){
            $condition = [
                'approve' => 1,
                'build' => 1,
                'country' => $params['country'],
                'city' => $params['city'],
                'date_start' => ['$lte' => $current_day],
                'date_end' => ['$gte' => $current_day]
            ];
        }
        
        $events = $this->getCollection()->find($condition,['name', 'date_start', 'date_end'])->sort(["date_start" => -1]);
        $test_category_set = [];

        foreach($events as $event){
            
            $event['id'] = $event['_id']->{'$id'};
            unset($event['_id']);

            $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
            $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);
            
            $picture = $this->getGalleryCollection()->findOne(['event_id' => $event['id']],['picture','detail']);
            $event['thumb'] = Image::load($picture['picture'])->toArrayResponse();
            $event['thumb']['detail'] = isset($picture['detail']) ? $picture['detail'] : '' ;

            $sniffer = $this->getSnifferCollection()->find(['event_id' => $event['id']]);
            $event['total_sniffer'] = $sniffer->count(true);
            
            $tag = $this->getEventTagCollection()->findOne(['event_id' => $event['id']]);
            $category = $this->getTagCollection()->findOne(['_id' => new \MongoId($tag['tag_id'])],[$lang['lang']]);
            $event['category_id'] = $category['_id']->{'$id'};
            $event['category_name'] = $category[$lang['lang']];
            
            $test_category_set[$category['_id']->{'$id'}][] = $event;
        }
        
        $final_item_lists = [];
        foreach ($test_category_set as $key => $item) {
            
            if(count($item) > 1){
                $item['0']['name'] = $item['0']['category_name'];
                $item['0']['id'] = $item['0']['category_id'];
                $item['0']['type'] = 'folder';
                
                unset($item['0']['category_id']);
                unset($item['0']['category_name']);
                unset($item['0']['total_sniffer']);
                $final_item_lists[] = $item['0'];
            }else{
                $item['0']['type'] = 'item';
                
                unset($item['0']['category_id']);
                unset($item['0']['category_name']);
                $final_item_lists[] = $item['0'];
            }
        }
        
        return $final_item_lists;
    }
    
    public function upcoming($options = [], Context $ctx) {
        
        $params = [
            'country' => RequestInfo::getHeader('country'),
            'city' => RequestInfo::getHeader('city')
        ];
        
        $limit = 20;
        if (!empty($options['limit'])) {
            $limit = $options['limit'];
        }
        
        $date = new \DateTime();
        $set_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_time = new \MongoDate($set_time);
        
        $condition = [
            'approve' => 1,
            'build' => 1,
            '$and' => [
                ['date_start' => ['$gte' => $current_time]],
                ['date_end' => ['$gte' => $current_time]]
            ]
        ];
        if($params['country'] !== false && $params['city'] !== false){
            $condition = [
                'approve' => 1,
                'build' => 1,
                '$and' => [
                    ['country' => $params['country']],
                    ['city' => $params['city']],
                    ['date_start' => ['$gte' => $current_time]],
                    ['date_end' => ['$gte' => $current_time]]
                ]
            ];
        }
        
        $items = $this->getCollection()->find($condition,['name','detail','date_start'])->sort(['date_start' => 1])->limit($limit);
        
        $res = [];
        foreach ($items as $item) {
            
            $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
            $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $thumb = $this->getGalleryCollection()->findOne(['event_id' => $item['id']],['picture']);
            $item['thumb'] = Image::load($thumb['picture'])->toArrayResponse();
            
            $sniffer = $this->getSnifferCollection()->find(['event_id' => $item['id']]);
            $item['total_sniffer'] = $sniffer->count(true);
            
            // For random an item
            $item['rand'] = rand(100000, 199999);
            $res[] = $item;
        }
        return $res;
    }
    
    public function category_set($category_id, Context $ctx) {
        
        $params = [
            'country' => RequestInfo::getHeader('country'),
            'city' => RequestInfo::getHeader('city')
        ];
        
        $date = new \DateTime();
        $current_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_day = new \MongoDate($current_time);
        
        $start_time = strtotime($date->format('Y-m-d').' 00:00:00');
        $start_of_day = new \MongoDate($start_time);
        
        $end_time = strtotime($date->format('Y-m-d').' 23:59:59');
        $end_of_day = new \MongoDate($end_time);
        
        $item_lists = [];
        
        $condition = [
            'approve' => 1,
            'build' => 1,
            'date_start' => ['$gt' => $start_of_day, '$lt' => $end_of_day]
        ];
        
        if($params['country'] !== false && $params['city'] !== false){
            $condition = [
                'approve' => 1,
                'build' => 1,
                'country' => $params['country'],
                'city' => $params['city'],
                'date_start' => ['$gt' => $start_of_day, '$lt' => $end_of_day]
            ];
        }
        
        // Filter by date_start only
        $first_items = $this->getCollection()->find($condition,['name','date_start'])
            ->sort(['date_start' => 1])
            ->limit(10);
        
        $check_duplicate_id = [];
        
        foreach ($first_items as $item) {
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $category = $this->getEventTagCollection()->findOne(['event_id' => $item['id']]);
            $item['category'] = $category['tag_id'];
            
            if ($category_id == $item['category']) {
                $check_duplicate_id[] = new \MongoId($item['id']);
                $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
                $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
                
                $thumb = $this->getGalleryCollection()->findOne(['event_id' => $item['id']],['picture']);
                $item['thumb'] = Image::load($thumb['picture'])->toArrayResponse();
                
                $sniffer = $this->getSnifferCollection()->find(['event_id' => $item['id']]);
                $item['total_sniffer'] = $sniffer->count(true);
                
                $item_lists[] = $item;
            }
        }
        
        $condition = [
            'approve' => 1,
            'build' => 1,
            '$and' => [
                ['date_start' => ['$lte' => $current_day]],
                ['date_end' => ['$gte' => $current_day]]
            ],
            '_id' => ['$nin' => $check_duplicate_id]
        ];
        
        if($params['country'] !== false && $params['city'] !== false){
            $condition = [
                'approve' => 1,
                'build' => 1,
                '$and' => [
                    ['country' => $params['country']],
                    ['city' => $params['city']],
                    ['date_start' => ['$lte' => $current_day]],
                    ['date_end' => ['$gte' => $current_day]]
                ],
                '_id' => ['$nin' => $check_duplicate_id]
            ];
        }
        
        $second_items = $this->getCollection()->find($condition, ['name','date_start'])
        ->sort(['date_start' => -1])
        ->limit(10);
        foreach ($second_items as $item) {
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $category = $this->getEventTagCollection()->findOne(['event_id' => $item['id']]);
            $item['category'] = $category['tag_id'];
            
            if ($category_id == $item['category']) {
                $check_duplicate_id[] = new \MongoId($item['id']);
                $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
                $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
                
                $thumb = $this->getGalleryCollection()->findOne(['event_id' => $item['id']],['picture']);
                $item['thumb'] = Image::load($thumb['picture'])->toArrayResponse();
                
                $sniffer = $this->getSnifferCollection()->find(['event_id' => $item['id']]);
                $item['total_sniffer'] = $sniffer->count(true);
                
                $item_lists[] = $item;
            }
        }
        
        return $item_lists;
    }
    
    public function category_upcoming($category_id, Context $ctx) {
        
        $params = [
            'country' => RequestInfo::getHeader('country'),
            'city' => RequestInfo::getHeader('city')
        ];
        
        $date = new \DateTime();
        $set_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_time = new \MongoDate($set_time);
        
        $new_lists = [];
        
        $event_tags = $this->getEventTagCollection()->find(['tag_id' => $category_id]);
        foreach ($event_tags as $item) {
            
            $condition = [
                'approve' => 1,
                'build' => 1,
                '_id' => new \MongoId($item['event_id']),
                'date_start' => ['$gt' => $current_time]
            ];
            
            if($params['country'] !== false && $params['city'] !== false){
                $condition = [
                    'approve' => 1,
                    'build' => 1,
                    'country' => $params['country'],
                    'city' => $params['city'],
                    '_id' => new \MongoId($item['event_id']),
                    'date_start' => ['$gt' => $current_time]
                ];
            }
            
            $event = $this->getCollection()->findOne($condition,['name', 'date_start', 'date_end']);
            
            if ($event !== null) {
                $event['id'] = $event['_id']->{'$id'};
                unset($event['_id']);

                $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
                $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);

                $picture = $this->getGalleryCollection()->findOne(['event_id' => $event['id']],['picture']);
                $event['thumb'] = Image::load($picture['picture'])->toArrayResponse();
                
                $sniffer = $this->getSnifferCollection()->find(['event_id' => $event['id']]);
                $event['total_sniffer'] = $sniffer->count(true);
            
                $new_lists[] = $event;
            }
        }
        
        return $new_lists;
    }
    
    public function search($word, Context $ctx) {
        
        if (empty($word)) {
            throw new ServiceException(ResponseHelper::notFound());
        }
        
        $date = new \DateTime();
        $set_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_time = new \MongoDate($set_time);
        
        $search = new \MongoRegex("/".str_replace(['"', "'", "\x22", "\x27"], '', $word)."/i");
        $items = $this->getCollection()->find([
            'approve' => 1,
            'build' => 1,
            'name' => $search,
            '$or' => [
                ['date_start' => ['$gte' => $current_time]],
                [
                    '$and' => [
                        ['date_start' => ['$lte' => $current_time]],
                        ['date_end' => ['$gte' => $current_time]]
                    ]
                ]
            ]
        ],['name','date_start','date_end'])->sort(['date_start' => -1]);
        
        $item_lists = [];
        if ($items->count() > 0) {
            foreach($items as $item){
                $item['id'] = $item['_id']->{'$id'};
                unset($item['_id']);
                
                $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
                $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);

                $picture = $this->getGalleryCollection()->findOne(['event_id' => $item['id']],['picture']);
                $item['thumb'] = Image::load($picture['picture'])->toArrayResponse();
                
                $sniffer = $this->getSnifferCollection()->find(['event_id' => $item['id']]);
                $item['total_sniffer'] = $sniffer->count(true);
                
                $item_lists[] = $item;
            }
        }
        
        return $item_lists;
    }
    
    public function get_advertise(Context $ctx) {
        try {
            
            $location = UserHelper::$default_location;
            $items = $this->getCollection()->find([
                'advertise.enable' => 1,
                'advertise.cities' => $location['1']
                ],['name','date_start','date_end','advertise']);
            $item_lists = [];
            foreach($items as $item){
                $item['id'] = $item['_id']->{'$id'};
                unset($item['_id']);
                
                // Get last Picture
                $picture = $this->getGalleryCollection()
                        ->find(['event_id' => $item['id']])
                        ->sort(['_id' => -1]) // Look like DESC in MySQL
                        ->limit(1);

                if ($picture->count(true)) {
                    foreach($picture as $pic){
                        $item['thumb'] = Image::load($pic['picture'])->toArrayResponse();
                    }
                }
                
                $sniffer = $this->getSnifferCollection()->find(['event_id' => $item['id']]);
                $item['total_sniffer'] = $sniffer->count(true);
            
                $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
                $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
                $item['advertise']['time_start'] = MongoHelper::dateToYmd($item['advertise']['time_start']);
                
                $item_lists[] = $item;
                
            }
            
            return $item_lists;
            
        } catch (\MongoException $e) {
            throw new ServiceException(ResponseHelper::error($e->getMessage(), $e->getCode()));
        }
    }
    
    public function edit_adverties($event_id, $params, Context $ctx) {
        
        $v = new Validator($params);
        $v->rule('required', ['enable', 'cities']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $params['time_start'] = new \MongoDate();
        $params['enable'] = intval($params['enable']);
        $set = [
            'advertise' => $params
        ];
        try {
            $this->getCollection()->update(['_id' => new \MongoId($event_id)],['$set' => $set]);
            $params['id'] = $event_id;
            unset($params['time_start']);
            return $params;
        } catch (\MongoException $e) {
            throw new ServiceException(ResponseHelper::error($e->getMessage(), $e->getCode()));
        }
    }
}
