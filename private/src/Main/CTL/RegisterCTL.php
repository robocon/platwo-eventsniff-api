<?php
/**
 * Created by PhpStorm.
 * User: MRG
 * Date: 10/18/14 AD
 * Time: 11:41 AM
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Helper\MongoHelper,
    Main\Service\UserService;


/**
 * @Restful
 * @uri /register
 */
class RegisterCTL extends BaseCTL {

    /**
     * @api {post} /register POST /register
     * @apiDescription Register with email
     * @apiName PostRegister
     * @apiGroup Resister
     * @apiParam {String} username Your username using for login to system
     * @apiParam {String} email Email address
     * @apiParam {String} password Your password
     * @apiParam {String} gender male or female
     * @apiParam {String} birth_date Your birth date
     * @apiParam {String} country Country id
     * @apiParam {String} city City id
     * @apiSuccessExample {json} Success-Response:
{
    "email":"demouser1423299557@hotmail.com",
    "username":"demo1423299557",
    "gender":"male",
    "birth_date":"2528-08-30 00:00:00",
    "country":"54b8dfa810f0edcf048b4567",
    "city":"54b8e0e010f0edcf048b4569",
    "display_name":"demo1423299557",
    "website":"",
    "mobile":"",
    "fb_id":"",
    "fb_name":"",
    "display_notification_number":0,
    "type":"normal",
    "setting":{
        "show_facebook":true,
        "show_website":true,
        "show_mobile":true,
        "show_gender":true,
        "show_birth_date":true,
        "show_email":true,
        "notify_update":true,
        "notify_message":true
    },
    "created_at":"2015-02-07 15:59:17",
    "last_login":"2015-02-07 15:59:17",
    "access_token":"b45bee792597912c5ba903443cac4cd8ff91616929297d70b0936f55d71cf8f5",
    "id":"54d5d3e510f0ed1f048b456a"
}
     * @POST
     */
    public function add(){
        try{
            $item = UserService::getInstance()->add($this->reqInfo->inputs(), $this->getCtx());
            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }
}