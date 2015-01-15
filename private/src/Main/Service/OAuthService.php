<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/27/14
 * Time: 2:42 PM
 */

namespace Main\Service;

use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;
use Facebook\GraphUser;
use Main\Context\Context;
use Main\DataModel\Image;
use Main\DB;
use Main\Exception\Service\ServiceException;
use Main\Helper\MongoHelper;
use Main\Helper\ResponseHelper;
use Main\Helper\UserHelper;
use Valitron\Validator;

class OAuthService extends BaseService {
    public function getUsersCollection(){
        $db = DB::getDB();
        return $db->users;
    }

    public function facebook($params, Context $ctx){
        file_put_contents('test.txt', print_r($params, true));

        FacebookSession::setDefaultApplication('717935634950887', 'b43f5c57b6af38948dbb3a6a4ce47eae');

        $v = new Validator($params);
        $v->rule('required', ['facebook_token']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $session = new FacebookSession($params['facebook_token']);
        try {
            /**
             * @var GraphUser $me;
             */
            $me = (new FacebookRequest(
                $session, 'GET', '/me'
            ))->execute()->getGraphObject(GraphUser::className());
            $me->getName();
            $fbId = $me->getId();
            $item = $this->getUsersCollection()->findOne(['fb_id'=> $fbId], ['access_token', 'type']);

            if(is_null($item)){
                $now = new \MongoTimestamp();
                $birth_date = $me->getBirthday();
                if($birth_date instanceof \DateTime){
                    $birth_date = $birth_date->getTimestamp();
                }
                else {
                    $birth_date = 0;
                }
                $birth_date = new \MongoTimestamp($birth_date);
                $item = [
                    '_id'=> new \MongoId(),
                    'fb_id'=> $fbId,
                    'fb_name'=> $me->getName(),
                    'display_name'=> $me->getName(),
                    'email'=> $me->getProperty('email'),
                    'username'=> $me->getId(),
                    'gender'=> $me->getProperty('gender'),
                    'birth_date'=> $birth_date,
                    'website'=> '',
                    'mobile'=> '',
                    'created_at'=> $now,
                    'updated_at'=> $now,
                    'type'=> 'normal',
                    'setting'=> UserHelper::defaultSetting(),
                    'display_notification_number' => 0,

                    // set default last login
                    'last_login' => new \MongoTimestamp()
                ];
                $item['access_token'] = $this->generateToken(MongoHelper::standardId($item['_id']));
//                $item['app_id'] = $ctx->getAppId();

                // get picture from facebook
                $pictureSource = file_get_contents('http://graph.facebook.com/'.$fbId.'/picture?type=large');
                if(strlen($pictureSource) == 0){
                    throw new ServiceException(ResponseHelper::error("Can't read facebook profile picture."));
                }
                $pic = Image::upload(base64_encode($pictureSource));
                $item['picture'] = $pic->toArray();

                $this->getUsersCollection()->insert($item);
                $this->getUsersCollection()->ensureIndex(['access_token'=> 1, 'app_id'=> 1]);
            }
            else if(!isset($item['access_token'])){
                $item['access_token'] = $this->generateToken(MongoHelper::standardId($item['_id']));
                $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$set'=> ['access_token'=> $item['access_token']]]);
                $this->getUsersCollection()->ensureIndex(['access_token'=> 1, 'app_id'=> 1]);
            }

            // remember device token
            if(isset($params['ios_device_token'])){
                // 
                file_put_contents('test.txt', $params['ios_device_token']);

                $hasToken = false;
                if(isset($item['ios_device_token']) && is_array($item['ios_device_token'])){
                    foreach($item['ios_device_token'] as $key=> $value){
                        if($value['type'] == $params['ios_device_token']['type']
                            && $value['key'] == $params['ios_device_token']['key']){
                            $hasToken = true;
                        }
                    }
                }
                if(!$hasToken){
                    $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$addToSet'=> ['ios_device_token'=> $params['ios_device_token'] ]]);
                }
            }
            if(isset($params['android_token'])){
                $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$addToSet'=> ['android_token'=> $params['android_token'] ]]);
            }

            return ['user_id'=> MongoHelper::standardId($item['_id']), 'access_token'=> $item['access_token'], 'type'=> $item['type']];

        } catch (FacebookRequestException $e) {
            throw new ServiceException(ResponseHelper::error($e->getMessage()));
        } catch (\Exception $e) {
            throw new ServiceException(ResponseHelper::error($e->getMessage()));
        }
    }

    public function password($params, Context $ctx){
        $v = new Validator($params);
        $v->rule('required', ['username', 'password']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }

        $item = $this->getUsersCollection()->findOne(['username'=> $params['username']]);
        if(is_null($item)){
            throw new ServiceException(ResponseHelper::notFound('Not found user'));
        }
        if(!isset($item['password']) || $item['password']!=md5($params['password'])){
            throw new ServiceException(ResponseHelper::error('Wrong password'));
        }
        if(!isset($item['access_token'])){
            $item['access_token'] = $this->generateToken(MongoHelper::standardId($item['_id']));
            $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$set'=> ['access_token'=> $item['access_token']]]);
            $this->getUsersCollection()->ensureIndex(['access_token'=> 1, 'app_id'=> 1]);
        }

        // remember device token
        if(isset($params['ios_device_token'])){
            if(isset($params['ios_device_token']['type']) && isset($params['ios_device_token']['key'])){
                $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$addToSet'=> ['ios_device_token'=> $params['ios_device_token'] ]]);
            }
        }
        if(isset($params['android_token'])){
            $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$addToSet'=> ['android_token'=> $params['android_token'] ]]);
        }

        // set last login
        $this->getUsersCollection()->update(['_id'=> $item['_id']], ['$set'=> ['last_login'=> new \MongoTimestamp()]]);

        return ['user_id'=> MongoHelper::standardId($item['_id']), 'access_token'=> $item['access_token'], 'type'=> $item['type']];
    }

    public function generateToken($id){
        return md5(uniqid($id, true));
    }

    public function logout($params, Context $ctx){
        $user = $ctx->getUser();
        if(is_null($user)){
            return ResponseHelper::notAuthorize();
        }
        if(isset($params['ios_device_token'])){
            $this->getUsersCollection()->update(['_id'=> $user['_id']], ['$pull'=> ['ios_device_token'=> $params['ios_device_token']]]);
        }
        if(isset($params['android_token'])){
            $this->getUsersCollection()->update(['_id'=> $user['_id']], ['$pull'=> ['android_token'=> $params['android_token']]]);
        }

        return array('success'=> true);
    }
}