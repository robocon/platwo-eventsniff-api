<?php

namespace Main\Helper;

use Main\DB,
    Main\DataModel\Image;

/**
 * Description of EventHelper
 *
 * @author robocon
 */
class EventHelper {
    
    public static function get_gallery($id){
        $db = DB::getDB();
        
        $items = $db->gallery->find(['event_id' => $id],['picture','detail']);
        $pictures = [];
        foreach($items as $item){
            $pic = Image::load_picture($item['picture']);
            $pic['detail'] = isset($item['detail']) ? $item['detail'] : '' ;
            $pictures[] = $pic;
        }
        return $pictures;
    }
    
    /**
     * 
     * @param type $id Event id
     * @param type $count Set true to show user detail
     * @return mixed users User diaplay_name and picture
     * @return int count Total user in this event
     */
    public static function get_sniffers($id, $count = false){
        $db = DB::getDB();
        $items = $db->sniffer->find(['event_id' => $id],['user_id'])->sort(['_id' => -1]);
        $res['count'] = $items->count(true);
        
        $sniff_user = [];
        foreach($items as $item){
            $sniff_user[] = new \MongoId($item['user_id']);
        }
        
        if($count === true){
            $user_lists = [];
            $users = $db->users->find([ '_id' => [ '$in' => $sniff_user ] ],['display_name','picture']);
            foreach($users as $user){
                $user['id'] = $user['_id']->{'$id'};
                unset($user['_id']);
                if(!isset($user['picture'])){
                    $user['picture'] = Image::default_profile_picture();
                }else{
                    $user['picture'] = Image::load_picture($user['picture']);
                }
                $user_lists[] = $user;
            }

            $res['users'] = $user_lists;
        }
        
        return $res;
    }
    
    
    public static function get_comments($id, $count = false){
        
        $db = DB::getDB();
        $items = $db->comment->find(['event_id' => $id],['user_id']);
        $res['count'] = $items->count(true);
        
        $filter_user = [];
        foreach($items as $item){
            $filter_user[] = new \MongoId($item['user_id']);
        }
        
        if($count === true){
            $user_lists = [];
            $users = $db->users->find([ '_id' => [ '$in' => $filter_user ] ],['display_name','picture']);
            foreach($users as $user){
                $user['id'] = $user['_id']->{'$id'};
                unset($user['_id']);
                if(!isset($user['picture'])){
                    $user['picture'] = Image::default_profile_picture();
                }else{
                    $user['picture'] = Image::load_picture($user['picture']);
                }
                $user_lists[] = $user;
            }

            $res['users'] = $user_lists;
        }
        
        return $res;
    }
    
    
    public static function get_check_in($lists) {
        
        $res['count'] = count($lists);
        $user_lists = [];
        
        $object_list = [];
        foreach($lists as $item){
            $object_list[] = new \MongoId($item);
        }
        
        $db = DB::getDB();
        $users = $db->users->find([ '_id' => [ '$in' => $object_list ] ],['display_name','picture']);
        
        foreach($users as $user){
            $user['id'] = $user['_id']->{'$id'};
            unset($user['_id']);
            if(!isset($user['picture'])){
                $user['picture'] = Image::default_profile_picture();
            }else{
                $user['picture'] = Image::load_picture($user['picture']);
            }
            $user_lists[] = $user;
        }
        
        $res['users'] = $user_lists;
        return $res;
    }
    
    public static function get_event_thumbnail($id){
        $db = DB::getDB();
        $item = $db->gallery->findOne(['event_id' => $id],['picture']);
        $item['id'] = $item['_id']->{'$id'};
        unset($item['_id']);
        
        $item['picture'] = Image::load_picture($item['picture']);
        $item['detail'] = isset($item['detail']) ? $item['detail'] : '' ;
        
        return $item;
    }
    
    
    public static function get_owner($id){
        $db = DB::getDB();
        $item = $db->users->findOne(['_id' => new \MongoId($id)],['display_name','picture','type']);
        if($item !== null){
            $item['id'] = $id;
            unset($item['_id']);
            $item['picture'] = Image::load_picture($item['picture']);
        }
           
        return $item;
    }
    
    public static function check_sniffed($user_id, $event_id){
        $db = DB::getDB();
        $test_sniff = $db->sniffer->findOne([
            'event_id' => $event_id,
            'user_id' => $user_id
        ],['_id']);
        $check = 'false';
        if($test_sniff != null){
            $check = 'true';
        }
        
        return $check;
    }
}
