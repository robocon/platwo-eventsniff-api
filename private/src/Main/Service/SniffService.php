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
    Main\DataModel\Image,
    Main\Helper\ArrayHelper,
    Main\Helper\MongoHelper,
    Main\Helper\ResponseHelper,
    Main\Helper\URL,
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
    
    public function getUsersCollection(){
         $db = DB::getDB();
        return $db->users;
    }
    
    public function gets($lang, $options = array(), Context $ctx) {
        
        // default lang is en
        $lang = empty($lang) ? 'en' : $lang;
        
        $items = $this->getCollection()->find([], [$lang]);
        $length = $items->count(true);
        
        $data = [];
        foreach($items as $item){
            $data[] = [
                'id' => $item['_id']->{'$id'}, 
                'name' => $item[$lang]
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
    
    public function follower($event_id, Context $ctx) {
        
        $item_lists = [];
        $items = $this->getSnifferCollection()->find(['event_id' => $event_id]);
        foreach ($items as $item) {
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $users = $this->getUsersCollection()->find(['_id' => new \MongoId($item['user_id'])],['display_name','picture']);
            foreach($users as $user){
                
                $user['id'] = $user['_id']->{'$id'};
                unset($user['_id']);
                unset($item['user_id']);

                $user['picture'] = Image::load($user['picture'])->toArrayResponse();

                $item['user'] = $user;
                $item_lists[] = $item;
            }
        }
        
        $res['data'] = $item_lists;
        $res['length'] = count($item_lists);
        return $res;
    }
}
