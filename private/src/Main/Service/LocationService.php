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
use Main\Helper\ResponseHelper;
use Valitron\Validator;

/**
 * Description of LocationService
 *
 * @author robocon
 */
class LocationService extends BaseService {

    public function getCollection(){
        $db = DB::getDB();
        return $db->location;
    }
    
    public function add($event_id, $params, Context $ctx) {
        
        $params['event_id'] = $event_id;
        
        $v = new Validator($params);
        $v->rule('required', ['event_id', 'location']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        $insert = ['name' => '', 'position' => $params['location'], 'event_id' => $event_id];
        $this->getCollection()->insert($insert);
        
        return $insert;
    }
}
