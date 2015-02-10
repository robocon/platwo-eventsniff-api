<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/22/14
 * Time: 3:09 PM
 */

namespace Main\CTL;
use Main\DataModel\Image;
use Main\Exception\Service\ServiceException;
use Main\Helper\MongoHelper;
use Main\Service\UserService;


/**
 * @Restful
 * @uri /user
 */
class UserCTL extends BaseCTL {
    /**
     * @api {get} /user/:user_id GET /user/:user_id
     * @apiDescription Get user detail in profile tab
     * @apiName GetUserDetail
     * @apiGroup User
     * @apiParam {String} user_id User Id
     * @apiSuccessExample {json} Success-Response:
{
    "birth_date": "1987-05-12 00:00:00",
    "display_name": "DemoDemo",
    "email": "justademo@gmail.com",
    "fb_id": "101265715789233",
    "fb_name": "Demo user",
    "gender": "male",
    "mobile": "",
    "picture": {
        "id": "54ba8cd690cc1350158b4619jpg",
        "width": 180,
        "height": 180,
        "url": "http://110.164.70.60/get/54ba8cd690aa1350158b4619jpg/"
    },
    "type": "normal",
    "username": "101265715789233",
    "website": "",
    "id": "54ba29c210f0edb8048b457a",
    "detail": ""
}
     * @GET
     * @uri /[h:id]
     */
    public function get(){
        try{
            $item = UserService::getInstance()->get($this->reqInfo->urlParam('id'), $this->getCtx());
            MongoHelper::standardId($item);
            
            // Make sure not send password back to user
            if(isset($item['password'])){
                unset($item['password']);
            }
            
            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @PUT
     * @uri /[h:id]
     */
    public function edit(){
        try{
            $item = UserService::getInstance()->edit($this->reqInfo->urlParam('id'), $this->reqInfo->params(), $this->getCtx());
            MongoHelper::standardId($item);
            if(isset($item['birth_date'])){
                $item['birth_date'] = MongoHelper::timeToInt($item['birth_date']);
            }
            if(isset($item['setting'])){
                unset($item['setting']);
            }
            if(isset($item['password'])){
                unset($item['password']);
            }
            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @PUT
     * @uri /change_password/[h:id]
     */
    public function changePassword(){
        try {
            return UserService::getInstance()->changePassword($this->reqInfo->urlParam('id'), $this->reqInfo->params(), $this->getCtx());
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @POST
     * @uri /request_reset_code
     */
    public function requestResetCode(){
        try {
            return UserService::getInstance()->requestResetCode($this->reqInfo->params(), $this->getCtx());
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @GET
     * @uri /get_user_by_code
     */
    public function getUserByCode(){
        try {
            $item = UserService::getInstance()->getUserByCode($this->reqInfo->params(), $this->getCtx());
            MongoHelper::standardIdEntity($item);

            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @POST
     * @uri /set_password_by_code
     */
    public function setPasswordByCode(){
        try {
            $item = UserService::getInstance()->setPasswordByCode($this->reqInfo->params(), $this->getCtx());

            return ['success'=> $item];
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @GET
     */
    public function gets(){
        try {
            $items = UserService::getInstance()->gets($this->reqInfo->params(), $this->getCtx());
            foreach($items['data'] as $key=> $item){
                MongoHelper::standardIdEntity($item);
                $item['created_at'] = MongoHelper::timeToInt($item['created_at']);
                $item['last_login'] = MongoHelper::timeToInt($item['last_login']);
                if(isset($item['picture'])){
                    $item['picture'] = Image::load($item['picture'])->toArrayResponse();
                }
                else {
                    $item['picture'] = null;
                }
                $items['data'][$key] = $item;
            }

            return $items;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @GET
     * @uri /admin
     */
    public function getAdmins(){
        try {
            $items = UserService::getInstance()->getAdmins($this->reqInfo->params(), $this->getCtx());
            foreach($items['data'] as $key=> $item){
                MongoHelper::standardIdEntity($item);
                $item['created_at'] = MongoHelper::timeToInt($item['created_at']);
                $item['last_login'] = MongoHelper::timeToInt($item['last_login']);
                if(isset($item['picture'])){
                    $item['picture'] = Image::load($item['picture'])->toArrayResponse();
                }
                else {
                    $item['picture'] = null;
                }
                $items['data'][$key] = $item;
            }

            return $items;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @POST
     * @uri /admin
     */
    public function addAdmin(){
        try{
            $item = UserService::getInstance()->addAdmin($this->reqInfo->inputs(), $this->getCtx());
            MongoHelper::standardId($item);
            if(isset($item['password'])){
                unset($item['password']);
            }
            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @DELETE
     * @uri /[h:id]
     */
    public function remove(){
        try{
            return UserService::getInstance()->remove($this->reqInfo->urlParam('id'), $this->getCtx());
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }
    
    /**
     * @api {get} /user/event/:user_id GET /user/event/:user_id
     * @apiDescription Get event that user was sniff
     * @apiName GetUserSnifferEvent
     * @apiGroup User
     * @apiParam {String} user_id User Id
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
        {
            "alarm": 0,
            "date_end": "2015-02-11 17:13:01",
            "date_start": "2015-02-04 17:13:01",
            "name": "test add name 1422439981",
            "id": "54c8b62d10f0ed1e048b4584",
            "picture": {
                "id": "54c9193a90cc13ac048b4638png",
                "width": 25,
                "height": 25,
                "url": "http://110.164.70.60/get/54c9193a90cc13ac048b4638png/"
            },
            "total_sniffer": 1
        },
        {...}
    ],
    "length": 4
}
     * @GET
     * @uri /event/[h:user_id]
     */
    public function event() {
        try {
            $items['data'] = UserService::getInstance()->event($this->reqInfo->urlParam('user_id'), $this->getCtx());
            $items['length'] = count($items['data']);
            
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /user/event/past/:user_id GET /user/event/past/:user_id
     * @apiDescription Get event that user was sniff in the past
     * @apiName GetUserPastEvent
     * @apiGroup User
     * @apiParam {String} user_id User Id
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
        {
            "date_end": "2015-02-04 10:57:27",
            "date_start": "2015-01-28 10:57:27",
            "name": "test add name 1422417447",
            "id": "54c85e2610f0ed1e048b4568",
            "picture": {
                "id": "54c8c13490cc13a8048b4619png",
                "width": 25,
                "height": 25,
                "url": "http://110.164.70.60/get/54c8c13490cc13a8048b4619png/"
            },
            "total_sniffer": 0
        },
        {...}
    ],
    "length": 4
}
     * @GET
     * @uri /event/past/[h:user_id]
     */
    public function past(){
        try {
            $items['data'] = UserService::getInstance()->past($this->reqInfo->urlParam('user_id'), $this->getCtx());
            $items['length'] = count($items['data']);
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @api {get} /user/event/pictures/:user_id GET /user/event/pictures/:user_id
     * @apiDescription Get pictures in each event from user
     * @apiName GetUserEventPicture
     * @apiGroup User
     * @apiParam {String} user_id User Id
     * @apiSuccessExample {json} Success-Response:
{
    "data": [
        {
            "date_end": "2015-02-13 15:52:56",
            "date_start": "2015-01-30 15:52:56",
            "name": "test add name 1422607976",
            "id": "54cb466710f0ed24048b4567",
            "picture_count": 4,
            "pictures": [
                {
                "id": "54cba97490cc1381588b4567png",
                "width": 25,
                "height": 25,
                "url": "http://110.164.70.60/get/54cba97490cc1381588b4567png/"
                },
                {... },
            ]
        },
        {...}
    ],
    "length": 2
}
     * @GET
     * @uri /event/pictures/[h:user_id]
     */
    public function pictures() {
        try {
            $items['data'] = UserService::getInstance()->pictures($this->reqInfo->urlParam('user_id'), $this->getCtx());
            $items['length'] = count($items['data']);
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
}