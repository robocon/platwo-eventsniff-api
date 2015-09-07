<?php

namespace Main\Service;

use Main\Context\Context,
    Main\DB,
    Main\Exception\Service\ServiceException,
    Main\Helper\MongoHelper,
    Main\Helper\ResponseHelper,
    Main\Helper\NotifyHelper,
    Main\Helper\EventHelper,
    Main\DataModel\Image;

class NotificationService extends BaseService {
    
    private $db = null;
    public function getDB(){
        
        if( $this->db === null ){
            $this->db = DB::getDB();
        }
        return $this->db;
    }
    
    public function get_notifications(Context $ctx) {
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::notAuthorize());
        }
        
        $db = $this->getDB();
        
        // Get last report if type is event or picture
        $last_report = $db->notify->find([
            '$or' => [
                ['object.type' => 'event'],
                ['object.type' => 'picture'],
            ],
            'owner' => $user['_id']->{'$id'}
        ])->sort(['created_at' => -1])->limit(1);
        
        $noti_items = [];
        foreach ($last_report as $key => $item) {

            $set_key =$item['created_at']->{'sec'};
            $pre_item = [
                'id' => $item['_id']->{'$id'},
                'title' => (isset($item['preview_header'])) ? $item['preview_header'] : '' ,
                'detail' => (isset($item['preview_content'])) ? $item['preview_content'] : '' ,
                'date' => date('Y-m-d H:i:s', $item['created_at']->{'sec'}),
                'thumb' => '',
                'type' => $item['object']['type']
            ];
            $noti_items[$set_key] = $pre_item;
        }
        
        // Get other reports 
        $items = $db->notify->find([
            '$and' => [
                ['object.type' => ['$ne' => 'event']],
                ['object.type' => ['$ne' => 'picture']]
            ],
            'owner' => $user['_id']->{'$id'}
        ]);
        foreach ($items as $key => $item) {
            $set_key = $item['created_at']->{'sec'};
            $type = $item['object']['type'];
            
            $thumb = '';
            if( $type == 'edit' OR $type == 'approve' ){
                $thumb = EventHelper::get_event_thumbnail($item['object']['id']->{'$id'});
            }
            $pre_item = [
                'id' => $item['_id']->{'$id'},
                'title' => (isset($item['preview_header'])) ? $item['preview_header'] : '' ,
                'detail' => (isset($item['preview_content'])) ? $item['preview_content'] : '' ,
                'date' => date('Y-m-d H:i:s', $item['created_at']->{'sec'}),
                'thumb' => $thumb,
                'type' => $type
            ];
            $noti_items[$set_key] = $pre_item;
            
        }
        ksort($noti_items);
        
        $res_items = [];
        foreach($noti_items as $key => $item){
            $res_items[] = $item;
        }
        
        return ['data' => $res_items, 'length' => count($res_items)];
    }
    
    public function get_reports(Context $ctx) {
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::notAuthorize());
        }
        
        $db = $this->getDB();
        
        $items = $db->notify->find([
            '$or' => [
                ['object.type' => 'event'],
                ['object.type' => 'picture'],
            ],
            'owner' => $user['_id']->{'$id'}
        ])->sort(['created_at' => -1]);
        
        $report_items = [];
        foreach ($items as $key => $item) {
            
            $thumb = $db->users->findOne(['_id' => $item['user_id']],['picture']);
            $thumb['picture'] = Image::load_picture($thumb['picture']);
            
            $pre_item = [
                'id' => $item['_id']->{'$id'},
                'title' => (isset($item['preview_header'])) ? $item['preview_header'] : '' ,
                'detail' => (isset($item['preview_content'])) ? $item['preview_content'] : '' ,
                'date' => date('Y-m-d H:i:s', $item['created_at']->{'sec'}),
                'thumb' => $thumb['picture'],
                'type' => $item['object']['type']
            ];
            $report_items[] = $pre_item;
        }
        
        return ['data' => $report_items, 'length' => count($report_items)];
    }
    
    public function get_report($id, Context $ctx) {
        
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::notAuthorize());
        }
        
        if( $id === null ){
            throw new ServiceException(ResponseHelper::validateError('Required report Id'));
        }
        
        $db = $this->getDB();
        $item = $db->notify->findOne(['_id' => new \MongoId($id)]);
        
        $pre_item = [
            'id' => $item['_id']->{'$id'},
            'title' => (isset($item['preview_header'])) ? $item['preview_header'] : '' ,
            'detail' => (isset($item['preview_content'])) ? $item['preview_content'] : '' ,
            'date' => date('Y-m-d H:i:s', $item['created_at']->{'sec'}),
            'thumb' => '',
            'type' => $item['object']['type']
        ];
            
        return $pre_item;
    }
}
