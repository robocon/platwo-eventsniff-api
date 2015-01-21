<?php
/**
 * Created by PhpStorm.
 * User: robocon
 * Date: 1/10/15
 * Time: 10:05 AM
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Helper\MongoHelper,
    Main\Service\EventService,
    Main\Service\GalleryService,
    Main\Service\TagService,
    Main\Service\LocationService;

/**
 * Class EventCTL
 * 
 * @package Main\CTL
 * @Restful
 * @uri /event
 */
class EventCTL extends BaseCTL {
    

    
    /**
     * @api {get} /event GET /event
     * @apiDescription Get all event
     * @apiName GetEvents
     * @apiGroup Event
     * @apiSuccessExample {json} Success-Response:
    {
        "length": 1,
        "total": 1,
        "data": [
            {
                "alarm": 0,
                "approve": 1,
                "build": 1,
                "credit": "https:\/\/www.google.com",
                "date_end": "1970-01-01 07:00:00",
                "date_start": "1970-01-01 07:00:00",
                "detail": "Example detail",
                "name": "Example title",
                "time_edit": "1970-01-01 07:00:00",
                "time_stamp": "1970-01-01 07:00:00",
                "user_id": "1",
                "id": "54ba191510f0edb7048b456a",
                "thumb": {
                    "id": "54ba7c3590cc13ab048b4628png",
                    "width": 100,
                    "height": 100,
                    "url": "http:\/\/110.164.70.60\/get\/54ba7c3590cc13ab048b4628png\/"
                }
            },
            { ... }
        ],
        "paging": {
        "page": 1,
        "limit": 15,
        "next": "http:\/\/eventsniff.dev\/event?page=2"
        }
    }
     * 
     * 
     * @GET
     */
    public function gets() {
        try {
            $items = EventService::getInstance()->gets($this->reqInfo->params(), $this->getCtx());
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /event/:event_id GET /event/:event_id
     * @apiDescription Get event from id [not complete yet]
     * @apiName GetEvent
     * @apiGroup Event
     * @apiSuccessExample {json} Success-Response:
    {
        "alarm": 0,
        "approve": 1,
        "build": 1,
        "credit": "https:\/\/www.google.com",
        "date_end": "1970-01-01 07:00:00",
        "date_start": "1970-01-01 07:00:00",
        "detail": "Example detail",
        "name": "Example title",
        "time_edit": "1970-01-01 07:00:00",
        "time_stamp": "1970-01-01 07:00:00",
        "user_id": "1",
        "id": "54ba191510f0edb7048b456a",
        "pictures": [
            {
                "id": "54ba7c3590cc13ab048b4628png",
                "width": 100,
                "height": 100,
                "url": "http:\/\/110.164.70.60\/get\/54ba7c3590cc13ab048b4628png\/"
            },
            {...}
        ],
        "total_sniffer": 2,
        "sniffer": [
        {
            "id": "54ba29c210f0edb8048b457a",
            "picture": {
                "id": "54ba8cd690cc1350158b4619jpg",
                "width": 180,
                "height": 180,
                "url": "http:\/\/110.164.70.60\/get\/54ba8cd690cc1350158b4619jpg\/"
            }
        }
        ],
        "total_comment": 2,
        "comments": [
            {
                "detail": "hello world",
                "user_id": "54ba29c210f0edb8048b457a",
                "event_id": "54ba191510f0edb7048b456a",
                "time_stamp": "2015-01-21 11:09:11",
                "id": "54bf266710f0ed12048b456a",
                "user": {
                    "display_name": "Kritsanasak Kuntaros",
                    "picture": {
                        "id": "54ba8cd690cc1350158b4619jpg",
                        "width": 180,
                        "height": 180,
                        "url": "http:\/\/110.164.70.60\/get\/54ba8cd690cc1350158b4619jpg\/"
                    }
                }
            },
            {...}
        ]
    }
     * 
     * @GET
     * @uri /[a:event_id] 
     */
    public function get() {
        try {
            $item = EventService::getInstance()->get($this->reqInfo->urlParam('event_id'), $this->getCtx());
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
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
     * @apiSuccessExample {json} Success-Response:
    {
        "event_id": "54b5e76510f0edc9068b4572",
        "user_id": "1",
        "id": "54bf399610f0ed11048b456b",
        "picture": {
            "id": "54bf9ca890cc13aa048b4617png",
            "width": 100,
            "height": 100,
            "url": "http:\/\/110.164.70.60\/get\/54bf9ca890cc13aa048b4617png\/"
        },
        "status": 200
    }
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
                'event_id' => $event['id'],
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
     * @api {post} /event/gallery/:event_id POST /event/gallery/:event_id
     * @apiDescription Save picture after first picture
     * @apiName PostAddEventGallery
     * @apiGroup Event
     * @apiParam {String} id Event id
     * @apiParam {String} picture Picture in base64_encode
     * @apiParam {String} user_id User id
     * @apiSuccessExample {json} Success-Response:
    {
        "picture": {
            "id": "54bf9d0b90cc13625e8b4577png",
            "width": 100,
            "height": 100,
            "url": "http:\/\/110.164.70.60\/get\/54bf9d0b90cc13625e8b4577png\/"
        },
        "user_id": "1",
        "event_id": "54b5e76510f0edc9068b4572",
        "id": "54b5e76510f0edc9068b4572",
        "status": 200
    }
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
            $res['event_id'] = $data['event_id'];
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
     * @apiSuccessExample {json} Success-Response:
{
    "name": "Example title",
    "detail": "Example detail",
    "date_start": "1970-01-01 07:00:00",
    "date_end": "1970-01-01 07:00:00",
    "credit": "https:\/\/www.google.com",
    "build": 1,
    "time_edit": "1970-01-01 07:00:00",
    "id": "54ba1bc910f0edb8048b456c",
    "tags": [
        {
            "tag_id": "6f2da37e72bf9e100b40567c",
            "id": "54bf3afc10f0ed11048b456d"
        },
        {...},
    ],
    "location": {
        "name": "",
        "position": "19.906496, 99.834254",
        "event_id": "54ba1bc910f0edb8048b456c",
        "id": "54bf3afc10f0ed11048b4570"
    },
    "status": 200
}
     * 
     * @PUT
     * @uri /[a:id]
     */
    public function edit() {
        
        try {
            
            // Update an event
            $res = EventService::getInstance()->edit($this->reqInfo->urlParam('id'), $this->reqInfo->params(), $this->getCtx());
            
            $res['date_start'] = MongoHelper::timeToStr($res['date_start']);
            $res['date_end'] = MongoHelper::timeToStr($res['date_start']);
            $res['time_edit'] = MongoHelper::timeToStr($res['date_start']);
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
    
    /**
     * 
     * @return type
     * 
     * @api {put} /event/alarm/:event_id/:active PUT /event/alarm/:event_id/:active
     * @apiDescription Update alarm an event
     * @apiName PutEventAlarm
     * @apiGroup Event
     * @apiParam {String} event_id Event id
     * @apiParam {Integer} active 0 is Disable, 1 is Enable
     * @apiSuccessExample {json} Success-Response:
        {
            "event_id": "54ba191510f0edb7048b456a",
            "active": 1
        }
     * 
     * @PUT
     * @uri /alarm/[a:event_id]/[i:active]
     */
    public function alarm() {
        try {
            $params = [
                'event_id' => $this->reqInfo->urlParam('event_id'),
                'active' => (int)$this->reqInfo->urlParam('active'),
            ];
            
            $update = EventService::getInstance()->alarm($params, $this->getCtx());
            return $update;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}