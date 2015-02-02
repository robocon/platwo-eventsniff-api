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
        
        // Upload to media server
        $upload = Image::upload($params['picture']);
            
        // Insert into MongoDB
        $this->getCollection()->insert([
            'picture' => $upload->toArray(), 
            'user_id' => $params['user_id'], 
            'event_id' => $params['event_id'],
            'detail' => ''
                ]);
        $picture = $upload->toArrayResponse();
        
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
        ]);
        
        $item_lists = [];
        foreach ($items as $item) {
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            unset($item['user_id']);
            unset($item['event_id']);
            
            $item['picture'] = Image::load($item['picture'])->toArrayResponse();
            
            $item_lists[] = $item;
        }
        
        return $item_lists;
    }
    
    /**
     * @todo get single picture
     * [] user detail
     * [] single picture detail
     */
    public function get($picture_id, Context $ctx) {
        
    }
}