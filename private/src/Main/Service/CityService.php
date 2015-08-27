<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\Service;

use Main\DB,
    Main\Context\Context;

/**
 * Description of CountryService
 *
 * @author robocon
 */
class CityService extends BaseService {
    public function getCollection(){
        $db = DB::getDB();
        return $db->cities;
    }
    
    private $db = null;
    public function getDB(){
        
        if( $this->db === null ){
            $this->db = DB::getDB();
        }
        return $this->db;
    }
    
    public function get($country_id, Context $ctx) {
        
        $db = $this->getDB();
        $date = new \DateTime();
        $current_time = new \MongoDate($date->getTimestamp());
        
        $data = [];
        $items = $db->cities->find(array("country_id"=> $country_id));
        foreach($items as $item){
            
            $count = $db->event->find([
                'approve' => 1,
                'build' => 1,
                'city' => $item['_id']->{'$id'},
                '$or' => [
                    ['date_start' => ['$gte' => $current_time]],
                    ['$and' => [
                        ['date_start' => ['$lte' => $current_time]],
                        ['date_end' => ['$gte' => $current_time]]
                    ]]
                ]
            ])->count(true);
            
            $data[] = [
                'id' => $item['_id']->{'$id'}, 
                'name' => $item['name'],
                'event_count' => $count,
            ];
        }
        return ['data' => $data, 'length' => count($data)];
    }
}
