<?php
/**
 * Created by PhpStorm.
 * User: robocon
 * Date: 1/10/15
 * Time: 12:29 PM
 */

namespace Main\Service;

use Main\Context\Context;
use Main\DataModel\Image;
use Main\DB;
use Main\Exception\Service\ServiceException;
use Main\Helper\ResponseHelper;
use Main\Helper\EventHelper;
use Valitron\Validator;

class GalleryService extends BaseService {

    public function getCollection(){
        $db = DB::getDB();
        return $db->gallery;
    }

    /**
     * Upload single picture
     * @param type $params
     * @param Context $ctx
     * @return type
     * @throws ServiceException
     */
    public function add($params, Context $ctx){

        $v = new Validator($params);
        $v->rule('required', ['picture', 'user_id', 'event_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        if (empty($params['detail'])) {
            $params['detail'] = '';
        }
        
        $db = DB::getDB();
        
        // Upload to media server
        $upload = Image::upload($params['picture']);
            
        $insert_data = [
            'picture' => $upload->toArray(), 
            'user_id' => $params['user_id'], 
            'event_id' => $params['event_id'],
            'detail' => $params['detail'],
            'advertise' => 1
        ];
        
        // Insert into MongoDB
        $db->gallery->insert($insert_data);
        $test_load = Image::load($insert_data['picture']);
        $picture = $test_load->toArrayResponse();
        $picture['detail'] = $insert_data['detail'];
        $picture['check_in'] = 'false';
        
        // Check-in when upload photo
        $date = new \DateTime();
        $current_time = strtotime($date->format('Y-m-d H:i:s'));
        $current_day = new \MongoDate($current_time);
        
        $condition = [
            '_id' => new \MongoId($params['event_id']),
            'approve' => 1,
            'build' => 1,
            '$and' => [
                ['date_start' => ['$lte' => $current_day]],
                ['date_end' => ['$gte' => $current_day]]
            ]
        ];
        $event = $db->event->findOne($condition,['_id']);
        
        if($event !== null){
            
            $db->event->update(['_id' => $event['_id']],['$addToSet' => ['check_in' => $params['user_id']]]);
            $picture['check_in'] = 'true';
        }
        
        return $picture;
    }
    
    public function gets($event_id, Context $ctx) {
        
        $v = new Validator(['event_id' => $event_id]);
        $v->rule('required', ['event_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $items = $this->getCollection()->find([
            'event_id' => $event_id
        ],['user_id','picture','detail']);
        
        $item_lists = [];
        foreach ($items as $item) {
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
//            $item['picture'] = Image::load($item['picture'])->toArrayResponse();
            $item['picture'] = Image::load_picture($item['picture']);
            $item['detail'] = isset($item['detail']) ? $item['detail'] : '' ;
            
            $owner = EventHelper::get_owner($item['user_id']);
            unset($item['user_id']);

            $item['user'] = $owner;
            if($owner === null){
                $item['user'] = '';
            }
                
            $item_lists[] = $item;
        }
        
        return $item_lists;
    }
    
    public function get($picture_id, Context $ctx) {
        
        $v = new Validator(['picture_id' => $picture_id]);
        $v->rule('required', ['picture_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $item = $this->getCollection()->findOne([
            '_id' => new \MongoId($picture_id)
        ],['picture','detail']);
        $item['id'] = $item['_id']->{'$id'};
        unset($item['_id']);
        unset($item['user_id']);
        
        $item['picture'] = Image::load($item['picture'])->toArrayResponse();
        if (empty($item['detail'])) {
            $item['detail'] = '';
        }
        return $item;
    }
    
    public function delete($picture_id, Context $ctx) {
        
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        $v = new Validator(['picture_id' => $picture_id]);
        $v->rule('required', ['picture_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $rows = $this->getCollection()->find([
            '_id' => new \MongoId($picture_id),
            'user_id' => $user['_id']->{'$id'}
            ])->count();

        if ($rows > 0) {
            $this->getCollection()->remove(['_id' => new \MongoId($picture_id)]);
            return ['success' => true];
        }else{
            throw new ServiceException(ResponseHelper::error('Not found your picture'));
        }
    }
}