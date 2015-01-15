<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 10/9/14
 * Time: 10:46 AM
 */

namespace Main\Service;


use Main\Context\Context;
use Main\DataModel\Image;
use Main\DB;
use Main\Exception\Service\ServiceException;
use Main\Helper\ArrayHelper;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Main\Helper\UpdatedTimeHelper;
use Main\Helper\URL;
use Valitron\Validator;

class ServiceService extends BaseService {
    public function getCollection(){
        return DB::getDB()->services;
    }

    public function addItem($params, Context $ctx){
        $v = new Validator($params);
        $v->rule('required', ['name', 'detail', 'price', 'cus_only', 'price_cus_only', 'pictures']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $insert = ArrayHelper::filterKey(['price', 'name', 'detail'], $params);

        if(isset($params['parent_id'])){
            if($this->getCollection()->count(['_id'=> MongoHelper::mongoId($params['parent_id']), 'type'=> 'folder']) == 0){
                throw new ServiceException(ResponseHelper::error('Not found parent_id'));
            }
            $insert['parent_id'] = new \MongoId($params['parent_id']);
        }
        else {
            $insert['parent_id'] = null;
        }

        $insert['pictures'] = [];
        foreach($params['pictures'] as $key=> $value){
            $insert['pictures'][] = Image::upload($value)->toArray();
        }
        $insert['pictures_length'] = count($insert['pictures']);

        $insert['cus_only'] = (bool)@$params['cus_only'];
        $insert['price_cus_only'] = (bool)@$params['price_cus_only'];

        if(isset($insert['price'])){
            $insert['price'] = (int)$insert['price'];
        }

        // seq insert
        $match = ['parent_id'=> null];
        if(!is_null($insert['parent_id'])){
            $match = ['parent_id'=> $insert['parent_id']];
        }
        $agg = $this->getCollection()->aggregate([
            ['$match'=> $match],
            ['$group'=> ['_id'=> null, 'max'=> ['$max'=> '$seq']]]
        ]);
        $insert['seq'] = (int)@$agg['result'][0]['max'] + 1;

        // insert type
        $insert['type'] = 'item';

        // insert view_count
        $insert['view_count'] = 0;

//        $insert['app_id'] = $ctx->getAppId();
        MongoHelper::setCreatedAt($insert);
        MongoHelper::setUpdatedAt($insert);

        $this->getCollection()->insert($insert);
        if(isset($insert['parent_id'])){
            $this->updateChildren($insert['parent_id']);
        }

        // service update timestamp (last_update)
        UpdatedTimeHelper::update('service', time());

        return $this->get($insert['_id'], $ctx);
    }

    public function editItem($id, $params, Context $ctx){
        $id = MongoHelper::mongoId($id);

        $set = ArrayHelper::filterKey(['name', 'detail', 'price', 'cus_only', 'price_cus_only', 'parent_id'], $params);

        if(isset($set['cus_only'])){
            $set['cus_only'] = (bool)@$set['cus_only'];
        }
        if(isset($set['price_cus_only'])){
            $set['price_cus_only'] = (bool)@$set['price_cus_only'];
        }
        if(isset($set['price'])){
            $set['price'] = (int)$set['price'];
        }
        if(isset($set['parent_id'])){
            if($this->getCollection()->count(['_id'=> MongoHelper::mongoId($set['parent_id']), 'type'=> 'folder']) == 0){
                throw new ServiceException(ResponseHelper::error('Not found parent_id'));
            }
            $set['parent_id'] = new \MongoId($set['parent_id']);
        }
        else {
            $set['parent_id'] = null;
        }

        MongoHelper::setUpdatedAt($set);

        // update
        $this->getCollection()->update(['_id'=> $id], ['$set'=> $set]);

        // feed update timestamp (last_update)
        UpdatedTimeHelper::update('service', time());

        return $this->get($id, $ctx);
    }

    public function addFolder($params, Context $ctx){
        $v = new Validator($params);
        $v->rule('required', ['name', 'thumb']);

        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $insert = ArrayHelper::filterKey(['name', 'thumb'], $params);

        if(isset($params['parent_id'])){
            if($this->getCollection()->count(['_id'=> MongoHelper::mongoId($params['parent_id']), 'type'=> 'folder']) == 0){
                throw new ServiceException(ResponseHelper::error('Not found parent_id'));
            }
            $insert['parent_id'] = new \MongoId($params['parent_id']);
        }
        else {
            $insert['parent_id'] = null;
        }

        $insert['thumb'] = Image::upload($params['thumb'])->toArray();
        $insert['children_length'] = 0;

//        $insert['app_id'] = $ctx->getAppId();
        MongoHelper::setCreatedAt($insert);
        MongoHelper::setUpdatedAt($insert);

        // seq insert
        $match = ['parent_id'=> null];
        if(!is_null($insert['parent_id'])){
            $match = ['parent_id'=> $insert['parent_id']];
        }
        $agg = $this->getCollection()->aggregate([
            ['$match'=> $match],
            ['$group'=> ['_id'=> null, 'max'=> ['$max'=> '$seq']]]
        ]);
        $insert['seq'] = (int)@$agg['result'][0]['max'] + 1;

        // insert type
        $insert['type'] = 'folder';

        $this->getCollection()->insert($insert);
        if(isset($insert['parent_id'])){
            $this->updateChildren($insert['parent_id']);
        }

        // service update timestamp (last_update)
        UpdatedTimeHelper::update('service', time());

        return $this->get($insert['_id'], $ctx);
    }

    public function editFolder($id, $params, Context $ctx){
        $id = MongoHelper::mongoId($id);

        $set = ArrayHelper::filterKey(['name', 'thumb', 'parent_id'], $params);

        if(isset($set['parent_id'])){
            if($this->getCollection()->count(['_id'=> MongoHelper::mongoId($set['parent_id']), 'type'=> 'folder']) == 0){
                throw new ServiceException(ResponseHelper::error('Not found parent_id'));
            }
            $set['parent_id'] = new \MongoId($set['parent_id']);
        }
        else {
            $set['parent_id'] = null;
        }

        if(isset($set['thumb'])){
            $set['thumb'] = Image::upload($set['thumb'])->toArray();
        }

        MongoHelper::setUpdatedAt($set);

        // update
        $this->getCollection()->update(['_id'=> $id], ['$set'=> $set]);

        // feed update timestamp (last_update)
        UpdatedTimeHelper::update('service', time());

        return $this->get($id, $ctx);
    }

    public function gets($options = array(), Context $ctx){
        $default = array(
            "page"=> 1,
            "limit"=> 15
        );
        $options = array_merge($default, $options);

        $skip = ($options['page']-1)*$options['limit'];

        // condition parent_id
        $condition = ['parent_id'=> null];
        if(isset($options['parent_id'])){
            $condition = ['parent_id'=> MongoHelper::mongoId($options['parent_id'])];
        }

        $cursor = $this->getCollection()
            ->find($condition)
            ->limit((int)$options['limit'])
            ->skip((int)$skip)
            ->sort(['seq'=> -1]);

        $data = [];
        foreach($cursor as $item){
            $data[] = $item;
        }

        $total = $this->getCollection()->count($condition);
        $length = $cursor->count(true);

        $res = array(
            'length'=> $length,
            'total'=> $total,
            'data'=> $data,
            'paging'=> [
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            ]
        );

        $pagingLength = $total/(int)$options['limit'];
        $pagingLength = floor($pagingLength)==$pagingLength? floor($pagingLength): floor($pagingLength) + 1;
        $res['paging']['length'] = $pagingLength;
        $res['paging']['current'] = (int)$options['page'];
        if(((int)$options['page'] * (int)$options['limit']) < $total){
            $nextQueryString = http_build_query(['page'=> (int)$options['page']+1, 'limit'=> (int)$options['limit']]);
            $res['paging']['next'] = URL::absolute('/service'.'?'.$nextQueryString);
        }

        // add last_update to response
        $lastUpdate = UpdatedTimeHelper::get('service');
        $res['last_updated'] = MongoHelper::timeToInt($lastUpdate['time']);

        return $res;
    }

    public function updateChildren($id){
        $count = $this->getCollection()->count(['parent_id'=> MongoHelper::mongoId($id)]);
        $this->getCollection()->update(['_id'=> MongoHelper::mongoId($id)], ['$set'=> ['children_length'=> $count]]);
    }

    public function get($id, Context $ctx){
        $item = $this->getCollection()->findOne(['_id'=> MongoHelper::mongoId($id)]);
        if(is_null($item)){
            throw new ServiceException(ResponseHelper::notFound());
        }

        return $item;
    }

    // pictures

    public function getPictures($id, $params, Context $ctx){
        $id = MongoHelper::mongoId($id);
        if($this->getCollection()->count(['_id'=> $id, 'type'=> 'item']) == 0){
            return ResponseHelper::notFound();
        }

//        $this->collection->update(['_id'=> $id], ['$setOnInsert'=> ['history'=> []]], ['upsert'=> true]);

        $default = ["page"=> 1, "limit"=> 15];
        $options = array_merge($default, $params);
        $arg = $this->getCollection()->aggregate([
            ['$match'=> ['_id'=> $id, 'type'=> 'item']],
            ['$project'=> ['pictures'=> 1]],
            ['$unwind'=> '$pictures'],
            ['$group'=> ['_id'=> null, 'total'=> ['$sum'=> 1]]]
        ]);

        $total = (int)@$arg['result'][0]['total'];
        $limit = (int)$options['limit'];
        $page = (int)$options['page'];

//        $slice = MongoHelper::createSlice($page, $limit, $total);
        $slice = [($page-1)*$page, $limit];

        if($slice[1] == 0){
            $data = [];
        }
        else {
            $entity = $this->getCollection()->findOne(['_id'=> $id, 'type'=> 'item'], ['pictures'=> ['$slice'=> $slice]]);
            $data = Image::loads($entity['pictures'])->toArrayResponse();
        }

        // reverse data
        // $data = array_reverse($data);

        $res = array(
            'length'=> count($data),
            'total'=> $total,
            'data'=> $data,
            'paging'=> array(
                'page'=> (int)$options['page'],
                'limit'=> (int)$options['limit']
            )
        );

        $pagingLength = $total/(int)$options['limit'];
        $pagingLength = floor($pagingLength)==$pagingLength? floor($pagingLength): floor($pagingLength) + 1;
        $res['paging']['length'] = $pagingLength;
        $res['paging']['current'] = (int)$options['page'];
        if(((int)$options['page'] * (int)$options['limit']) < $total){
            $nextQueryString = http_build_query(['page'=> (int)$options['page']+1, 'limit'=> (int)$options['limit']]);
            $res['paging']['next'] = URL::absolute('/service/'.MongoHelper::standardId($id).'/picture?'.$nextQueryString);
        }

        return $res;
    }

    public function addPictures($id, $params, Context $ctx){
        $id = MongoHelper::mongoId($id);
        $v = new Validator($params);
        $v->rule('required', ['pictures']);
        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        if($this->getCollection()->count(['_id'=> $id, 'type'=> 'item']) == 0){
            return ResponseHelper::notFound();
        }

        $res = [];
        foreach($params['pictures'] as $value){
            $img = Image::upload($value);
            $this->getCollection()->update(['_id'=> $id, 'type'=> 'item'], ['$push'=> ['pictures'=> $img->toArray()]]);
            $res[] = $img->toArrayResponse();
        }

        return $res;
    }

    public function deletePictures($id, $params, Context $ctx){
        $id = MongoHelper::mongoId($id);
        $v = new Validator($params);
        $v->rule('required', ['id']);
        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        if($this->getCollection()->count(['_id'=> $id, 'type'=> 'item']) == 0){
            return ResponseHelper::notFound();
        }

        $res = [];
        foreach($params['id'] as $value){
            $arg = $this->getCollection()->aggregate([
                ['$match'=> ['_id'=> $id, 'type'=> 'item']],
                ['$project'=> ['pictures'=> 1]],
                ['$unwind'=> '$pictures'],
                ['$group'=> ['_id'=> null, 'total'=> ['$sum'=> 1]]]
            ]);

            $total = (int)@$arg['result'][0]['total'];
            if($total==1){
                break;
            }

            $this->getCollection()->update(['_id'=> $id, 'type'=> 'item'], ['$pull'=> ['pictures'=> ['id'=> $value]]]);
            $res[] = $value;
        }

        return $res;
    }

    public function delete($id, Context $ctx){
        $id = MongoHelper::mongoId($id);

        $this->getCollection()->remove(array("_id"=> $id));

        // feed update timestamp (last_update)
        UpdatedTimeHelper::update('service', time());

        return array("success"=> true);
    }

    public function sort($param){
        $v = new Validator($param);
        $v->rule('required', ['id', 'offset']);
        if(!$v->validate()){
            return ResponseHelper::validateError($v->errors());
        }

        foreach($param['id'] as $key=> $id){
            $mongoId = MongoHelper::mongoId($id);
            $seq = $key+$param['offset'];
            $this->getCollection()->update(array('_id'=> $mongoId), array('$set'=> array('seq'=> $seq)));
        }
        return array('success'=> true);
    }

    public function incView($id){
        $this->getCollection()->update(['_id'=> MongoHelper::mongoId($id)], ['$inc'=> ['view_count'=> 1]]);
    }
}