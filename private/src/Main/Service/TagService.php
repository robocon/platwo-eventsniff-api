<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\Service;

use Main\Context\Context;
use Main\DB;
use Main\Exception\Service\ServiceException;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Valitron\Validator;

/**
 * Description of TagService
 *
 * @author robocon
 */
class TagService extends BaseService {
    
    public function getCollection(){
        $db = DB::getDB();
        return $db->event_tag;
    }
    
    public function add($event_id, $params, Context $ctx) {
        
        $params['event_id'] = $event_id;
        
        $v = new Validator($params);
        $v->rule('required', ['event_id', 'tags']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $res = [];
        foreach($params['tags'] as $tag){
            $insert = ['tag_id' => $tag, 'event_id' => $event_id];
            $this->getCollection()->insert($insert);
            MongoHelper::standardIdEntity($insert);
            $res[] = ['tag_id' => $tag, 'id' => $insert['id']];
        }
        
        return $res;
    }
}
