<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/22/14
 * Time: 2:35 PM
 */

namespace Main\CTL;
use Main\Exception\Service\ServiceException;
use Main\Service\OAuthService;

/**
 * @Restful
 * @uri /oauth
 */
class OAuthCTL extends BaseCTL {
    /**
     * 
     * @api {post} /oauth/facebook POST /oauth/facebook
     * @apiDescription Register with facebook
     * @apiName OauthFacebook
     * @apiGroup OAuth
     * @apiParam {String} facebook_token
     * @apiParam {String} ios_device_token Token from your mobile
     * @apiParam {String} android_token Token from your mobile
     * @apiParam {String} country Country id
     * @apiParam {String} city City id
     * @apiSuccessExample {json} Success-Response:
     * {
     *  "user_id": "54506d34da354df2078b4569",
     *  "access_token": "9f0f853517eaaed3c0b74838e6e95693",
     *  "type": "normal",
     * }
     * 
     * @POST
     * @uri /facebook
     */
    public function facebook(){
        try{
            $item = OAuthService::getInstance()->facebook($this->reqInfo->inputs(), $this->getCtx());
            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @POST
     * @uri /password
     */
    public function password(){
        try{
            $item = OAuthService::getInstance()->password($this->reqInfo->inputs(), $this->getCtx());
            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }

    /**
     * @GET
     */
    public function token(){
        return $this->getCtx()->getUser();
    }

    /**
     * @POST
     * @GET
     * @uri /logout
     */
    public function logout(){
        try{
            return OAuthService::getInstance()->logout($this->reqInfo->inputs(), $this->getCtx());
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }
}