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
     * @uri /follower/[h:event_id]
     */
    public function gets() {
        
        exit;
    }
        
    /**
     * @api {post} /sniff/follow/:event_id/:user_id POST /sniff/follow/:event_id/:user_id
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
     * @uri /follow/[h:event_id]/[h:user_id]
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
     * @api {delete} /sniff/follow/:event_id/:user_id DELETE /sniff/follow/:event_id/:user_id
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
     * @uri /follow/[h:event_id]/[h:user_id]
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
     * @api {get} /sniff/category/:lang GET /sniff/category/:lang
     * @apiDescription Get all category
     * @apiName SniffCategory
     * @apiGroup Sniff
     * @apiParam {String} lang Language like en, th. Default is en
     * @apiSuccessExample {json} Success-Response:
     * {
     *      "length": 20,
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
     * @uri /category/[a:lang]
     */
    public function category() {
        try {
            $categories = SniffService::getInstance()->gets($this->reqInfo->urlParam('lang'), array(), $this->getCtx());
            return $categories;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }

}
