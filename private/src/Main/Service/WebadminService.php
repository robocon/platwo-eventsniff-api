<?php

namespace Main\Service;

use Main\Context\Context,
        Main\DB,
        Main\Exception\Service\ServiceException,
        Main\Helper\MongoHelper,
        Main\Helper\ResponseHelper,
        Main\Helper\NotifyHelper;

class WebadminService extends BaseService {
    
    private $db = null;
    public function getDB(){
        
        if( $this->db === null ){
            $this->db = DB::getDB();
        }
        return $this->db;
    }
    
    public function approve($params, Context $ctx) {
        
        $db = $this->getDB();
        
        $item = $db->event->findOne(['_id' => new \MongoId($params['event_id'])],['name','user_id']);
        if( !$item ){
            throw new ServiceException(ResponseHelper::notFound());
        }
        
        $user = $db->users->findOne(['_id' => new \MongoId($item['user_id'])]);
        $msg = 'กิจกรรม "'.$item['name'].'" ได้รับการอนุมัติแล้ว';
        
        $entity = NotifyHelper::create($item['_id'], "event_approve", "ข้อความจากระบบ", $msg, $user['_id']);
        NotifyHelper::incBadge($user['_id']->{'$id'});
        $user['display_notification_number']++;

        $args = [
            'id'=> MongoHelper::standardId($entity['_id']),
            'object_id'=> MongoHelper::standardId($item['_id']),
            'type'=> "event_approve"
        ];

        if(!$user['setting']['notify_message']){
            ResponseHelper::error('Notification message not enable');
        }

        NotifyHelper::send($user, $entity['preview_content'], $args);
        
        $res = [
            'event_id' => $params['event_id'],
            'msg' => $msg
        ];
        return $res;
    }
}
