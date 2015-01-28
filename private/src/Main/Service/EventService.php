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
    Main\Helper\URL,
    Valitron\Validator;

class EventService extends BaseService {

    public function getCollection(){
        $db = DB::getDB();
        return $db->event;
    }

    public function getGalleryCollection(){
         $db = DB::getDB();
        return $db->gallery;
    }

    public function getSnifferCollection(){
         $db = DB::getDB();
        return $db->sniffer;
    }

    public function getCommentCollection(){
         $db = DB::getDB();
        return $db->comment;
    }

    public function getUsersCollection(){
         $db = DB::getDB();
        return $db->users;
    }

    public function getTagCollection(){
         $db = DB::getDB();
        return $db->tag;
    }

    public function getEventTagCollection(){
         $db = DB::getDB();
        return $db->event_tag;
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
            $res['paging']['next'] = URL::absolute('/event'.'?'.  http_build_query(['page' => (int)$options['page']+1]));

            if ($options['page'] > 1) {
                $res['pagging']['prev'] = URL::absolute('/event'.'?'.  http_build_query(['page' => (int)$options['page']]));
            }
        }

        return $res;
    }

    public function get($id, Context $ctx) {

        $id = MongoHelper::mongoId($id);
        $item = $this->getCollection()->findOne(['_id' => $id]);

        $item['id'] = $item['_id']->{'$id'};
        unset($item['_id']);

        $item['date_end'] = MongoHelper::dateToYmd($item['date_end']);
        $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
        $item['time_edit'] = MongoHelper::dateToYmd($item['time_edit']);
        $item['time_stamp'] = MongoHelper::dateToYmd($item['time_stamp']);

        // Get latest 5 pictures
        $gallery = $this->getGalleryCollection()
            ->find(['event_id' => $item['id']])
            ->sort(['_id' => -1])
            ->limit(5);
        $item['pictures'] = [];

        if ($gallery->count(true)) {
            $pictures = [];
            foreach ($gallery as $picture) {
                $pictures[] = Image::load($picture['picture'])->toArrayResponse();
            }
            $item['pictures'] = $pictures;
        }

        // Get latest 20 sniffer
        $sniffers = $this->getSnifferCollection()
            ->find(['event_id' => $item['id']])
            ->sort(['_id' => -1])
            ->limit(20);
        $item['total_sniffer'] = $this->getSnifferCollection()->find(['event_id' => $item['id']])->count();
        $item['sniffer'] = [];
        if ($sniffers->count(true)) {
            $user_lists = [];
            foreach($sniffers as $sniffer){
                $sniffer['id'] = $sniffer['_id']->{'$id'};
                unset($sniffer['_id']);

                // Get user detail
                $user = $this->getUsersCollection()->findOne(array("_id" => MongoHelper::mongoId($sniffer['user_id'])));
                $user_lists[] = [
                    'id' => $user['_id']->{'$id'},
                    'picture' => Image::load($user['picture'])->toArrayResponse()
                ];
            }
            $item['sniffer'] = $user_lists;
        }

        // get latest 3 comment
        $comment_lists = $this->getCommentCollection()
            ->find(['event_id' => $item['id']])
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
                $user = $this->getUsersCollection()->findOne(array("_id" => MongoHelper::mongoId($comment['user_id'])));
                $comment['user'] = [
                    'display_name' => $user['display_name'],
                    'picture' => Image::load($user['picture'])->toArrayResponse()
                ];
                $comment['time_stamp'] = MongoHelper::timeToStr($comment['time_stamp']);

                $comments[] = $comment;
            }
            $item['comments'] = $comments;
        }

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
        $params['time_stamp'] = new \MongoTimestamp();
        $params['alarm'] = 0;
        $insert = ArrayHelper::filterKey(['user_id', 'build', 'approve', 'time_stamp', 'alarm'], $params);

        $this->getCollection()->insert($insert);
        return $insert;
    }

    public function edit($id, $params, Context $ctx) {

        // Set this ID First
        $id = MongoHelper::mongoId($id);

        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'date_start', 'date_end', 'credit']);

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

        $this->getCollection()->update(['_id'=> $id], ['$set'=> $set]);
        unset($set['build']);
        return $set;
    }

    public function alarm($params, Context $ctx) {

        $v = new Validator($params);
        $v->rules([
                'required' => [ ['event_id'], ['active'] ],
                'integer' => [ ['active'] ],
                'length' => [['active', 1]]
            ]);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        // Set this ID First
        $event_id = MongoHelper::mongoId($params['event_id']);

        $find_event = $this->getCollection()->findOne(['_id' => $event_id]);
        if ($find_event === null) {
            return ResponseHelper::error("Can not find this event :(");
        }

        $this->getCollection()->update(
            ['_id' => $event_id],
            ['$set'=> [
                'alarm' => $params['active']
                ]
            ]);

        return $params;
    }

    public function category_list($category_lists, Context $ctx) {

        $new_lists = [];

        $date = new \DateTime();
        $set_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_time = new \MongoDate($set_time);

        foreach($category_lists as $category){

            $event_tags = $this->getEventTagCollection()->find(['tag_id' => $category['id']]);

            // Count an event from event_tag
            $event_tags_count = $event_tags->count(true);

            if ($event_tags_count > 0) {

                foreach($event_tags as $tag){

                    // Find an event_id and filter with date_start must less than current date
                    $event = $this->getCollection()->findOne([
                        '_id' => new \MongoId($tag['event_id']),
                        'date_start' => ['$gt' => $current_time]
                    ],['_id']);

                    if($event !== null){

                        $picture = $this->getGalleryCollection()->findOne([
                            'event_id' => $event['_id']->{'$id'}
                        ],['picture']);

                        $category['thumb'] = Image::load($picture['picture'])->toArrayResponse();
                        $new_lists[] = $category;
                    }
                }
            }
        }


        return $new_lists;
    }

    public function now($lang, Context $ctx) {

        $date = new \DateTime();
        $set_time = strtotime($date->format('Y-m-d').' 00:00:00');
        $current_time = new \MongoDate($set_time);
        
        $end_time = strtotime($date->format('Y-m-d').' 23:59:59');
        $end_of_day = new \MongoDate($end_time);
        
        $item_lists = [];
        $total_event = 0;

        // Filter by date_start only
        $first_items = $this->getCollection()->find([
                'approve' => 1,
                'build' => 1,
                'date_start' => ['$gte' => $current_time, '$lte' => $end_of_day]
            ],['name','date_start'])
            ->sort(['date_start' => 1])
            ->limit(10);

        if ($first_items->count(true)) {
            foreach ($first_items as $item) {
                $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
                $item['type'] = 'item';
                $item['id'] = $item['_id']->{'$id'};
                unset($item['_id']);

                $thumb = $this->getGalleryCollection()->findOne(['event_id' => $item['id']],['picture']);
                $item['thumb'] = Image::load($thumb['picture'])->toArrayResponse();

                $category = $this->getEventTagCollection()->findOne(['event_id' => $item['id']]);
                $item['category'] = $category['tag_id'];

                $item_lists[] = $item;
                $total_event++;
            }
        }
        
        // Filter by date_start until date_end
        $second_items = $this->getCollection()->find([
            'approve' => 1,
            'build' => 1,
            'date_start' => ['$lte' => $current_time],
            'date_end' => ['$gte' => $current_time]
        ], ['name','date_start'])
        ->sort(['date_start' => -1])
        ->limit(10);

        if ($second_items->count(true)) {
            foreach ($second_items as $item) {
                $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
                $item['type'] = 'item';
                $item['id'] = $item['_id']->{'$id'};
                unset($item['_id']);

                $thumb = $this->getGalleryCollection()->findOne(['event_id' => $item['id']],['picture']);
                $item['thumb'] = Image::load($thumb['picture'])->toArrayResponse();

                $category = $this->getEventTagCollection()->findOne(['event_id' => $item['id']]);
                $item['category'] = $category['tag_id'];

                $item_lists[] = $item;
                $total_event++;
            }
        }

        $new_lists = [];
        for ($i=0; $i < $total_event; $i++) {
            $event = $item_lists[$i];

            $prev = empty($item_lists[$i-1]) ? false : $item_lists[$i-1];
            $next = empty($item_lists[$i+1]) ? false : $item_lists[$i+1];

            if ( ($prev === false OR $prev['category'] != $event['category']) && $event['category'] != $next['category']){
                $new_lists[] = $event;

            }elseif (($prev === false OR $prev['category'] != $event['category']) && $event['category'] == $next['category']) {
                
                $thumb = $this->getTagCollection()->findOne(['_id' => new \MongoId($event['category'])],[$lang]);
                
                $event['type'] = 'category';
                $new_lists[] = $event;

            }elseif ($event['category'] == $prev['category'] && ($event['category'] != $next['category'] OR $next === false) ) {
                unset($item_lists[$i]);
                
            }else{
                $new_lists[] = $event;
            }
        }

        if ($total_event <= 10) {
            return $item_lists;
        }else {
            /**
             * @todo group an event
             */
            return $new_lists;
        }
    }
}
