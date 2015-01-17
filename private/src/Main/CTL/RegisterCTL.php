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
     * @apiName Register
     * @apiGroup User
     * @apiParam {String} username Your username using for login to system
     * @apiParam {String} email Email address
     * @apiParam {String} password Your password
     * @apiParam {String} gender male or female
     * @apiParam {String} birth_date Your birth date
     * 
     * @POST
     */
    public function add(){
        try{
            $item = UserService::getInstance()->add($this->reqInfo->inputs(), $this->getCtx());
            MongoHelper::standardId($item);
            if(isset($item['birth_date'])){
                $item['birth_date'] = MongoHelper::timeToInt($item['birth_date']);
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
}