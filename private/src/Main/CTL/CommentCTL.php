<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Helper\MongoHelper,
    Main\Service\CommentService;

/**
 * Description of CommentCTL
 *
 * @author robocon
 * 
 * @package Main\CTL
 * @Restful
 * @uri /comment
 */
class CommentCTL extends BaseCTL {
    
    /**
     * 
     * @param type $param
     * 
     * @api {post} /comment/:event_id POST /comment/:event_id
     * @apiDescription Comment into event
     * @apiName CommentSave
     * @apiGroup Comment
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "detail": "hello world",
     *  "user_id": "54ba29c210f0edb8048b457a",
     *  "event_id": "54ba191510f0edb7048b456a",
     *  "time_stamp": "2015-01-21 11:09:11",
     *  "user": {
     *      "name": "Kritsanasak Kuntaros",
     *      "picture": {
     *          "id": "54ba8cd690cc1350158b4619jpg",
     *          "width": 180,
     *          "height": 180,
     *          "url": "http:\/\/110.164.70.60\/get\/54ba8cd690cc1350158b4619jpg\/"
     *      }
     *  },
     *  "id": "54bf266710f0ed12048b456a"
     * }
     * 
     * @POST
     * @uri /[a:event_id]
     */
    public function save() {
        try {
            
            $params = $this->reqInfo->params();
            $params['event_id'] = $this->reqInfo->urlParam('event_id');

            $item = CommentService::getInstance()->save($params, $this->getCtx());
            MongoHelper::standardIdEntity($item);
            
            $item['time_stamp'] = MongoHelper::timeToStr($item['time_stamp']);
            
            return $item;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * 
     * @return type
     * 
     * @GET
     * @uri /[a:event_id]
     */
    public function gets() {
        try {
            
            var_dump('PAUSE!!!');
            exit;
//            $params['event_id'] = $this->reqInfo->urlParam('event_id');

            $item = CommentService::getInstance()->gets($this->reqInfo->urlParam('event_id'), $this->reqInfo->params(), $this->getCtx());
//            MongoHelper::standardIdEntity($item);
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
