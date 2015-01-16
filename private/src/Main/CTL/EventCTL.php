<?php
/**
 * Created by PhpStorm.
 * User: robocon
 * Date: 1/10/15
 * Time: 10:05 AM
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException;
use Main\Helper\MongoHelper;
use Main\Service\EventService;
use Main\Service\GalleryService;
use Main\Service\TagService;
use Main\Service\LocationService;

/**
 * Class EventCTL
 * 
 * @package Main\CTL
 * @Restful
 * @uri /event
 */
class EventCTL extends BaseCTL {
    
    /**
     * @GET
     */
    public function gets() {
        
    }

    /**
     * @POST
     */
    public function add(){
        try{

            $item = EventService::getInstance()->add($this->reqInfo->params(), $this->getCtx());
            MongoHelper::standardIdEntity($item);

            // Store image into gallery
            $data = [
                'pictures' => [
                    $item['thumb']
                ],
                'user_id' => $item['user_id'],
                'event_id' => $item['id'],
            ];
            $pictures = GalleryService::getInstance()->add($data, $this->getCtx());

            EventService::getInstance()->updateThumb($item['id'], $pictures['0'], $this->getCtx());

            /**
             * @TODO
             * - Add into tag
             */
            return $item;

        } catch(ServiceException $e) {
            return $e->getResponse();
        }
    }

    /**
     * Save data from mobile that has send a picture and user_id
     *
     * @api {post} /event/gallery POST /event/gallery
     * @apiDescription Booking event with first picture
     * @apiName PostEventGallery
     * @apiGroup Event
     * @apiParam {String} picture Picture in base64_encode
     * @apiParam {String} user_id User id
     * 
     * @POST
     * @uri /gallery
     */
    public function event_gallery(){
        
        try {
            
            // Add into event with only user_id for the first time
            $event = EventService::getInstance()->mobile_add($this->reqInfo->params(), $this->getCtx());
            MongoHelper::standardIdEntity($event);
        
            // Store image into gallery
            $data = [
                'picture' => $this->reqInfo->param('picture'),
                'user_id' => $event['user_id'],
                'event_id' => $event['id'],
            ];
            
            $res = [
                'user_id' => $event['user_id'],
                'id' => $event['id'],
            ];
            
            $res['picture'] = GalleryService::getInstance()->add($data, $this->getCtx());
            $res['status'] = 200;
            return $res;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {post} /event/gallery/:id POST /event/gallery/:id
     * @apiDescription Save picture after first picture
     * @apiName PostAddEventGallery
     * @apiGroup Event
     * @apiParam {String} id Event id
     * @apiParam {String} picture Picture in base64_encode
     * @apiParam {String} user_id User id
     * 
     * @POST
     * @uri /gallery/[a:id]
     */
    public function add_event_gallery() {
        try {
            
            $data = [
                'picture' => $this->reqInfo->param('picture'),
                'user_id' => $this->reqInfo->param('user_id'),
                'event_id' => $this->reqInfo->urlParam('id')
            ];
            
            $res = [];
            $res['picture'] = GalleryService::getInstance()->add($data, $this->getCtx());
            $res['user_id'] = $data['user_id'];
            $res['id'] = $data['event_id'];
            $res['status'] = 200;
            return $res;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * Update event data from eventdetail
     * 
     * @api {put} /event/:id PUT /event/:id
     * @apiDescription Update event details
     * @apiName PutEvent
     * @apiGroup Event
     * @apiParam {String} id Event id
     * @apiParam {String} name Event name
     * @apiParam {Text} detail Event description
     * @apiParam {String} date_start Event datetime E.g. 2014-01-15 11:00:00
     * @apiParam {String} date_end Event datetime
     * @apiParam {String} credit Something where are you get this event from 
     * @apiParam {String} user_id User id
     * 
     * @PUT
     * @uri /[a:id]
     */
    public function edit() {
        
        try {
            
            // Update an event
            $res = EventService::getInstance()->edit($this->reqInfo->urlParam('id'), $this->reqInfo->params(), $this->getCtx());
            
            $res['date_start'] = MongoHelper::timeToInt($res['date_start']);
            $res['date_end'] = MongoHelper::timeToInt($res['date_start']);
            $res['time_edit'] = MongoHelper::timeToInt($res['date_start']);
            $res['id'] = $this->reqInfo->urlParam('id');
            
            $res['tags'] = TagService::getInstance()->add($this->reqInfo->urlParam('id'), $this->reqInfo->params(), $this->getCtx());

            $location = LocationService::getInstance()->add($this->reqInfo->urlParam('id'), $this->reqInfo->params(), $this->getCtx());
            MongoHelper::standardIdEntity($location);
            $res['location'] = $location;
            $res['status'] = 200;
            return $res;
            
        } catch (ServiceException $e) {
            return $e->getResponse();
        }



            
    }
}