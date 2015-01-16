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
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Valitron\Validator;

/**
 * Description of SniffService
 *
 * @author robocon
 */
class SniffService extends BaseService {
    //put your code here
    
    public function getCollection(){
        $db = DB::getDB();
        return $db->tag;
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
}
