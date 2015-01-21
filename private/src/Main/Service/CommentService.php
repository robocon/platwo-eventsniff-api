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
    Valitron\Validator;

/**
 * Description of CommentService
 *
 * @author robocon
 */
class CommentService extends BaseService {
    
    public function getCollection(){
        $db = DB::getDB();
        return $db->comment;
    }
    
    public function getUsersCollection(){
         $db = DB::getDB();
        return $db->users;
    }
    
    public function save($params, Context $ctx) {
        $v = new Validator($params);
        $v->rule('required', ['detail', 'event_id', 'user_id']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $params['time_stamp'] = new \MongoTimestamp();
        
        $insert = ArrayHelper::filterKey(['detail', 'event_id', 'user_id', 'time_stamp'], $params);
        $this->getCollection()->insert($insert);
        
        // Get user detail
        $user = $this->getUsersCollection()->findOne(array("_id" => MongoHelper::mongoId($insert['user_id'])));
        $insert['user'] = [
            'display_name' => $user['display_name'],
            'picture' => Image::load($user['picture'])->toArrayResponse()
        ];
        
        return $insert;
    }
    
    public function gets($event_id, $options = [], Context $ctx) {
        
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
        
        var_dump($event_id);
        var_dump($options);
        exit;
    }
}
