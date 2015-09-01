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
    Main\Helper\MongoHelper,
    Main\Helper\ResponseHelper,
    Main\Helper\NotifyHelper,
    Valitron\Validator;


class ReportService extends BaseService {
    
    private $db = null;
    public function getDB(){
        
        if( $this->db === null ){
            $this->db = DB::getDB();
        }
        return $this->db;
    }
    
    public function save($params, Context $ctx) {
        
        $check_user = $ctx->getUser();
        if(!$check_user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        $v = new Validator($params);
        $v->rule('required', ['detail', 'type', 'reference_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $db = $this->getDB();
        $db->report->insert($params);
        $params['id'] = $params['_id']->{'$id'};
        $user = $db->users->findOne(['_id' => new \MongoId($params['user_id'])]);
        
        // SEND NOTIFICATION
        $entity = NotifyHelper::create($params['reference_id'], $params['type'], "ข้อความจากระบบ", $params['detail'], $user['_id']);
        NotifyHelper::incBadge($user['_id']->{'$id'});
        $user['display_notification_number']++;

        $args = [
            'id'=> MongoHelper::standardId($entity['_id']),
            'object_id'=> MongoHelper::standardId($params['_id']),
            'type'=> "event_approve"
        ];

        if(!$user['setting']['notify_message']){
            ResponseHelper::error('Notification message not enable');
        }

        NotifyHelper::send($user, $entity['preview_content'], $args);
        // END SEND NOTIFICATION
        
        unset($params['_id']);
        
        return $params;
    }
}
