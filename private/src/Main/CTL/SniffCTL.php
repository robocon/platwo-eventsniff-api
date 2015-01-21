<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Helper\MongoHelper,
    Main\Service\SniffService;

/**
 * @Restful
 * @uri /sniff
 */
class SniffCTL extends BaseCTL {
    
    /**
     * @GET
     * @uri /follower/[a:event_id]
     */
    public function gets() {
        
        exit;
    }
        
    /**
     * @api {post} /follow/:event_id/:user_id POST /follow/:event_id/:user_id
     * @apiDescription Follow an event
     * @apiName SniffFollow
     * @apiGroup Sniff
     * @apiParam {String} event_id Event id
     * @apiParam {String} user_id User id
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "event_id":"54ba191510f0edb7048b456a",
     *  "user_id":"54ba29c210f0edb8048b457a",
     *  "id":"54be2e6610f0ed53058b456b"
     * }
     * 
     * @POST
     * @uri /follow/[a:event_id]/[a:user_id]
     */
    public function follow() {
        
        $params = [];
        $params['event_id'] = $this->reqInfo->urlParam('event_id');
        $params['user_id'] = $this->reqInfo->urlParam('user_id');
        
        try {
            $item = SniffService::getInstance()->follow($params, $this->getCtx());
            MongoHelper::standardIdEntity($item);
            
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
        
    }
    
    /**
     * @api {delete} /follow/:event_id/:user_id DELETE /follow/:event_id/:user_id
     * @apiDescription Unfollow an event
     * @apiName SniffUnfollow
     * @apiGroup Sniff
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "event_id":"54ba191510f0edb7048b456a",
     *  "user_id":"54ba29c210f0edb8048b457a",
     * }
     * 
     * @DELETE
     * @uri /follow/[a:event_id]/[a:user_id]
     */
    public function unfollow() {
        
        $params = [];
        $params['event_id'] = $this->reqInfo->urlParam('event_id');
        $params['user_id'] = $this->reqInfo->urlParam('user_id');
        
        try {
            $item = SniffService::getInstance()->unfollow($params, $this->getCtx());
            MongoHelper::standardIdEntity($item);
            
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /sniff/category GET /sniff/category
     * @apiDescription Get all category
     * @apiName SniffCategory
     * @apiGroup Sniff
     * @apiSuccessExample {json} Success-Response:
     * {
     *      "length": 19,
     *      "data": [
     *          {
     *              "id": "6f2da37e72bf9e100b40567c",
     *              "name" "Awards",
     *          },
     *          {
     *              "id": "e9c5c932c205770da433d3de",
     *              "name" "Conferences",
     *          },
     *          ...
     *      ]
     * }
     * 
     * @GET
     * @uri /category
     */
    public function category() {
        try {
            $categories = SniffService::getInstance()->get(array(), $this->getCtx());
            return $categories;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }

}
