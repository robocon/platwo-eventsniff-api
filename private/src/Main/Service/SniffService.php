<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\Service;

use Main\Context\Context,
    Main\DB,
    Main\Exception\Service\ServiceException,
    Main\Helper\ArrayHelper,
    Main\Helper\MongoHelper,
    Main\Helper\ResponseHelper,
    Valitron\Validator;

/**
 * Description of SniffService
 *
 * @author robocon
 */
class SniffService extends BaseService {
    
    public function getCollection(){
        $db = DB::getDB();
        return $db->tag;
    }
    
    public function getSnifferCollection(){
        $db = DB::getDB();
        return $db->sniffer;
    }
    
    public function get($options = array(), Context $ctx) {
        $items = $this->getCollection()->find();
        $length = $items->count(true);
        
        $data = [];
        foreach($items as $item){
            $data[] = [
                'id' => $item['_id']->{'$id'}, 
                'name' => $item['name']
            ];
        }
        
        $res = [
            'length' => $length,
            'data' => $data,
        ];
        return $res;
    }
    
    
    public function follow($params, Context $ctx) {
        $v = new Validator($params);
        $v->rule('required', ['event_id', 'user_id']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        // Filter to check user and event exist?
        $search = $this->filter_follow($params);
        
        if ($search === null) {
            $insert = ArrayHelper::filterKey(['event_id', 'user_id'], $params);
            $this->getSnifferCollection()->insert($insert);
            
            return $insert;
        }else{
            return ResponseHelper::error("User already exist in this event");
        }
        
    }
    
    public function unfollow($params, Context $ctx) {
        $v = new Validator($params);
        $v->rule('required', ['event_id', 'user_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        // Filter to check user and event exist?
        $search = $this->filter_follow($params);
        
        if ($search !== null) {
            $delete = ArrayHelper::filterKey(['event_id', 'user_id'], $params);
            $this->getSnifferCollection()->remove($delete);
        
            return $delete;
        }else{
            return ResponseHelper::error("Can not find this user and event :(");
        }
        
    }
    
    public function filter_follow($params){
        // Filter to check user and event exist?
        $search = $this->getSnifferCollection()->findOne([
            'event_id' => $params['event_id'],
            'user_id' => $params['user_id'],
        ]);
        
        return $search;
    }
}
