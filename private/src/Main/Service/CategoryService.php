<?php

namespace Main\Service;

use Main\DB,
    Main\Helper\ResponseHelper,
    Main\Helper\MongoHelper,
    Main\DataModel\Image,
    Main\Context\Context;

class CategoryService extends BaseService {

    public $db = null;
    private function connect(){
        if($this->db == null){
            $this->db = DB::getDB();
        }
        
        return $this->db;
    }
    
    /**
     * Sniff and Unsniff Category
     * 
     * @param type $params
     * @param Context $ctx
     * @return type
     */
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
    
    /**
     * Show categories with default picture and event rows
     * 
     * @param Context $ctx
     * @return type
     */
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
                $item['picture'] = Image::default_category_picture();
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
    
    public function get_events($id, Context $ctx) {
        
        $user = $ctx->getUser();
        if($user === null){
            return ResponseHelper::error('Access denied');
        }
        
        $db = $this->connect();
        $dt = new \DateTime();
        $time_stamp = $dt->getTimestamp();
        $date_now = new \MongoDate($time_stamp);
        
        $condition = [
            'build' => 1,
            'approve' => 1,
            'date_end' => [ '$gte' => $date_now ]
        ];
        
        if(isset($user['sniffing_around']) && !empty($user['sniffing_around'])){
            $condition['city'] = ['$in' => $user['sniffing_around']];
        }
        
        $key_lists = [];
        $events = $db->event->find($condition, ['_id']);
        foreach($events as $event){
            
            // Key for match 
            $key_lists[] = $event['_id']->{'$id'};
        }
        
        // Search an event in key_lists
        $filter_events = $db->event_tag->find([
            'tag_id' => $id,
            'event_id' => [ '$in' => $key_lists ]
        ],['event_id']);
        
        $hour_start = strtotime($dt->format('Y-m-d 00:00:00'));
        $hour_end = strtotime($dt->format('Y-m-d 23:59:59'));
        $active_events = [];
        $inactive_events = [];
        
        foreach($filter_events as $item){
            
            $event = $db->event->findOne(['_id' => new \MongoId($item['event_id'])],['name', 'detail', 'date_start', 'date_end', 'picture']);
            
            $event['id'] = $event['_id']->{'$id'};
            
            // Using this for sort an event
            $event['time_sort'] = $event_timestamp = $event['date_start']->sec;
            
            $event['date_start'] = MongoHelper::dateToYmd($event['date_start']);
            $event['date_end'] = MongoHelper::dateToYmd($event['date_end']);
            
            $thumb = $db->gallery->findOne(['event_id' => $event['id']],['picture']);
            $event['thumb'] = Image::load($thumb['picture'])->toArrayResponse();
            
            $sniffers = $db->sniffer->find(['event_id' => $event['id']]);
            $sniff_users = [];
            $event['sniffed'] = false;
            foreach($sniffers as $sniff){
                $one_user = $db->users->findOne(['_id' => new \MongoId($sniff['user_id'])],['display_name','picture']);
                
                if($user['_id']->{'$id'} == $one_user['_id']->{'$id'}){
                    $event['sniffed'] = true;
                }
                
                if(!isset($one_user['picture'])){
                    $one_user['picture'] = Image::default_profile_picture();
                }
                $one_user['picture'] = Image::load_picture($one_user['picture']);
                unset($one_user['_id']);
                $sniff_users[] = $one_user;
            }
            $event['sniffer'] = $sniff_users;
            $event['total_sniffer'] = $sniffers->count(true);
            
            unset($event['_id']);
            
            // Find an event between two times
            if($event_timestamp > $hour_start && $event_timestamp < $hour_end){
                $active_events[] = $event;
            }else{
                $inactive_events[] = $event;
            }
        }
        
        usort($active_events, function($a, $b) {
            return $a['time_sort'] - $b['time_sort'];
        });
        
        usort($inactive_events, function($a, $b) {
            return $a['time_sort'] - $b['time_sort'];
        });
        
        $res = array_merge_recursive($active_events, $inactive_events);
        
        return ['data' => $res, 'length' => count($res)];
    }
}
