<?php

namespace Main\Service;

use Main\DB,
    Main\Helper\ResponseHelper,
    Main\DataModel\Image,
    Main\Context\Context;

/**
 * Description of CategoryService
 *
 * @author robocon
 */
class CategoryService extends BaseService {
//    public $db = null;
//    public function __construct() {
//        
//    }
    public $db = null;
    private function connect(){
        if($this->db == null){
            $this->db = DB::getDB();
        }
        
        return $this->db;
    }
    
    public function sniff_category($params, Context $ctx){
        
        $user = $ctx->getUser();
        if($user === null){
            return ResponseHelper::error('Access denied');
        }
        
        $status = (bool) $params['status'];
        
        $db = $this->connect();
        if($status == true){
            
            // add into sniff_category
            $db->users->update(['_id' => $user['_id']],['$addToSet' => ['sniff_category' => $params['category_id']]]);
            
        }else{
            
            // remove from sniff_category
            $db->users->update(['_id' => $user['_id']],['$pull' => ['sniff_category' => $params['category_id']]]);
            
        }
        
        return ['success' => true];
    }
    
    public function get_categorys(Context $ctx) {
        $user = $ctx->getUser();
        if($user === null){
            return ResponseHelper::error('Access denied');
        }
        
        $db = $this->connect();
        $dt = new \DateTime();
        $time_stamp = $dt->getTimestamp();
        $date_now = new \MongoDate($time_stamp);
        
        // Set an event into array
        $event_lists = [];
        $events = $db->event->find([
            'build' => 1,
            'approve' => 1,
            'date_end' => [ '$gte' => $date_now ]
        ],['date_end']);
        foreach($events as $event){
            $event_lists[] = $event['_id']->{'$id'};
        }
        
        $categories = [];
        $items = $db->tag->find([],['en']);
        foreach($items as $item){
            $item['id'] = $item['_id']->{'$id'};
            $item['name'] = $item['en'];
            
            // sniff status
            $item['sniffed'] = false;
            if(in_array($item['id'], $user['sniff_category'])){
                $item['sniffed'] = true;
            }
            
            // Search an event from event_tag in event(Array)
            $events = $db->event_tag->find(['tag_id' => $item['id']],['event_id']);
            $count_active_event = 0;
            foreach($events as $event){
                if(in_array($event['event_id'], $event_lists)){
                    $count_active_event++;
                }
            }
            $item['rows'] = $count_active_event;

            // - Default picture
            if(!isset($item['picture'])){
                $item['picture'] = [
                    'id' => '558b2b1990cc13a7048b4597png',
                    'width' => '1000',
                    'height' => '1000'
                ];
            }
            $item['picture'] = Image::load_picture($item['picture']);
            
            unset($item['_id']);
            unset($item['en']);
            
            $categories[] = $item;
        }
        
        $res = [
            'data' => $categories,
            'length' => count($categories)
        ];
        
        return $res;
    }
    
    
    public function event_in_category($id) {
        $db = $this->connect();
        
    }
    
    
    // @todo
        // - Query Active to one array
        // - Query Inactive Event
        // - And then merge
    public function get_events($id, Context $ctx) {
        
        dump('ASDFASDFASDF');
        exit;
    }
}
