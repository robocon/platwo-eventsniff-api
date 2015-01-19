<?php
/**
 * Created by PhpStorm.
 * User: robocon
 * Date: 1/10/15
 * Time: 10:27 AM
 */

namespace Main\Service;

use Main\Context\Context,
    Main\DB,
    Main\Exception\Service\ServiceException,
    Main\Helper\ArrayHelper,
    Main\Helper\MongoHelper,
    Main\Helper\ResponseHelper,
    Main\Helper\URL,
    Valitron\Validator;

class EventService extends BaseService {

    public function getCollection(){
        $db = DB::getDB();
        return $db->event;
    }
    
    public function gets($options = [], Context $ctx) {
        
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
        
        $data = [];
        foreach ($items as $item) {
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            $item['date_end'] = MongoHelper::timeToStr($item['date_end']);
            $item['date_start'] = MongoHelper::timeToStr($item['date_start']);
            $item['time_edit'] = MongoHelper::timeToStr($item['time_edit']);
            $item['time_stamp'] = MongoHelper::timeToStr($item['time_stamp']);
            
            $data[] = $item;
        }
        
        $res = [
            'length' => $length,
            'total' => $total,
            'data' => $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        ];
        
        if ($length > 0 && $length <= $total ) {
            $res['paging']['next'] = URL::absolute('/event'.'?'.  http_build_query(['page' => (int)$options['page']+1]));
        
            if ($options['page'] > 1) {
                $res['pagging']['prev'] = URL::absolute('/event'.'?'.  http_build_query(['page' => (int)$options['page']]));
            }
        }
        
        return $res;
    }
    
    /**
     * Add from website
     * 
     * @param type $params
     * @param Context $ctx
     * @return type
     * @throws ServiceException
     */
    public function add($params, Context $ctx){


        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'date_start', 'date_end', 'approve', 'thumb', 'credit', 'user_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $data = [
            'name' => $params['name'],
            'detail' => $params['detail'],
            'date_start' => $params['date_start'],
            'date_end' => $params['date_end'],
            'approve' => $params['approve'],
            'thumb' => $params['thumb'],
            'credit' => $params['credit'],
            'user_id' => $params['user_id'],
            'time_stamp' => $params['time_stamp'],
            'time_edit' => $params['time_stamp']
        ];

        $insert = ArrayHelper::filterKey(['name', 'detail', 'date_start', 'date_end', 'approve', 'thumb', 'credit', 'user_id', 'time_stamp', 'time_edit'], $data);
        $this->getCollection()->insert($insert);

        return $insert;
    }

    public function updateThumb($id, $picture, Context $ctx){

        // Set this ID First
        $id = MongoHelper::mongoId($id);
        $set['thumb'] = $picture;

        $this->getCollection()->update(['_id'=> $id], ['$set'=> $set]);
    }
    
    /**
     * Add event from mobile
     * 
     * @param array $params
     * @param Context $ctx
     * @return type
     * @throws ServiceException
     */
    public function mobile_add($params, Context $ctx) {
        
        $v = new Validator($params);
        $v->rule('required', ['user_id']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        // Set build and approve to 0 if send from mobile
        $params['build'] = 0;
        $params['approve'] = 0;
        $params['time_stamp'] = new \MongoTimestamp();
        $params['alarm'] = 0;
        $insert = ArrayHelper::filterKey(['user_id', 'build', 'approve', 'time_stamp', 'alarm'], $params);
        
        $this->getCollection()->insert($insert);
        return $insert;
    }
    
    public function edit($id, $params, Context $ctx) {
        
        // Set this ID First
        $id = MongoHelper::mongoId($id);
        
        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'date_start', 'date_end', 'credit']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $set['name'] = $params['name'];
        $set['detail'] = $params['detail'];
        $set['date_start'] = new \MongoTimestamp(strtotime($params['date_start']));
        $set['date_end'] = new \MongoTimestamp(strtotime($params['date_end']));
        $set['credit'] = $params['credit'];
        $set['build'] = 1;
        $set['time_edit'] = new \MongoTimestamp();
        
        $this->getCollection()->update(['_id'=> $id], ['$set'=> $set]);
        
        return $set;
    }
}