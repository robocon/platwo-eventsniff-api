<?php
/**
 * Created by PhpStorm.
 * User: robocon
 * Date: 1/10/15
 * Time: 12:37 PM
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException;
use Main\Service\EventService;
use Main\Service\GalleryService;

/**
 * Class EventCTL
 * @package Main\CTL
 * @Restful
 * @uri /gallery
 */
class GalleryCTL extends BaseCTL {

    /**
     * @todo Same as /event/gallery
     * 
     * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * !!! PLEASE CHECK IT AGAIN !!!
     * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * 
     * @POST
     */
    public function add(){
        try{     

            $items = GalleryService::getInstance()->add($this->reqInfo->params(), $this->getCtx());
            return $items;

        } catch(ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /gallery/:event_id GET /gallery/event_id
     * @apiDescription Get all picture from event_id
     * @apiName GetPictures
     * @apiGroup Gallery
     * @apiParam {String} event_id Event id
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
    {
        "picture": {
            "id": "54c8c13490cc13a8048b4619png",
            "width": 25,
            "height": 25,
            "url": "http://110.164.70.60/get/54c8c13490cc13a8048b4619png/"
        },
        "id": "54c85e2610f0ed1e048b4569"
    },
    {...}
    ],
    "length": 2
}
     * 
     * @GET
     * @uri /[h:event_id]
     */
    public function gets() {
        try {
            
            $items['data'] = GalleryService::getInstance()->gets($this->reqInfo->urlParam('event_id'), $this->getCtx());
            $items['length'] = count($items['data']);
            return $items;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @GET
     * @uri /picture/[h:picture_id]
     */
    public function get() {
        try {
            
            $item = GalleryService::getInstance()->get($this->reqInfo->urlParam('picture_id'), $this->getCtx());
            return $item;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @todo Add delete picture
     * @DELETE
     * @uri /picture/[h:picture_id]
     */
    public function delete() {
        
        
        exit;
    }
}