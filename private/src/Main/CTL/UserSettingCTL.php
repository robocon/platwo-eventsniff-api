<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 8/23/14
 * Time: 2:16 PM
 */

namespace Main\CTL;


use Main\Exception\Service\ServiceException;
use Main\Service\UserSettingService;

/**
 * @Restful
 * @uri /user/setting
 */
class UserSettingCTL extends BaseCTL {
    /**
     * @GET
     * @uri /[h:id]
     */
    public function get(){
        try{
            $item = UserSettingService::getInstance()->get($this->reqInfo->urlParam('id'), $this->getCtx());
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
            $item = UserSettingService::getInstance()->edit($this->reqInfo->urlParam('id'), $this->reqInfo->params(), $this->getCtx());
            return $item;
        }
        catch(ServiceException $ex){
            return $ex->getResponse();
        }
    }
    
    /**
     * @api {put} /user/setting/:user_id/:field PUT /user/setting/:user_id/:field
     * @apiDescription Get user detail in profile tab
     * @apiName PutUserSetting
     * @apiGroup User
     * @apiParam {String} user_id User Id
     * @apiParam {String} field Allow for facebook, website, phone, gender, birth
     * @apiSuccessExample {json} Success-Response:
     * {"success":true}
     * @PUT
     * @uri /[h:user_id]/[*:field]
     */
    public function user_setting(){
        try {
            $params = [
                'user_id' => $this->reqInfo->urlParam('user_id'),
                'field' => $this->reqInfo->urlParam('field'),
                'enable' => $this->reqInfo->param('enable'),
            ];
            
            $res = UserSettingService::getInstance()->user_setting($params, $this->getCtx());
            return ['success' => $res];

        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}