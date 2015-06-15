<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 10/6/14
 * Time: 11:24 AM
 */

namespace Main\Helper;

use Main\Exception\Service\ServiceException,
    Main\Helper\ResponseHelper,
    Main\DB,
    Main\Http\RequestInfo;

class UserHelper {
    
    public static $user_id = null;
    public static $default_location = null;
    private static $group_role = null;
    private $data = null;
    
    public function __construct($data_user = null) {
        $this->data = $data_user;
    }
    
    public static function defaultSetting(){
        return [
            'show_facebook'=> true,
            'show_website'=> true,
            'show_mobile'=> true,
            'show_gender'=> true,
            'show_birth_date'=> true,
            'show_email'=> true, // Fix for show only

            'notify_update'=> true,
            'notify_message'=> true
        ];
    }
    
    public static function notification_setting() {
        return [
            'alarm' => true,
            'event_update' => true,
            'category_add_event' => true,
            'like_checkin' => true,
        ];
    }


    public static function generate_key() {
        return hash('sha256', time().uniqid().SITE_BLOWFISH);
    }
    
    public static function generate_token($user_id, $user_private_key){
        return hash('sha256', $user_id.uniqid().$user_private_key.uniqid());
    }
    
    public static function generate_password($password, $user_private_key){
        return hash('sha256', $password.SITE_BLOWFISH.$user_private_key);
    }
    
    /**
     * Check user token from db
     * 
     * @return boolean
     */
    public static function check_token(){
        
        $token = RequestInfo::getToken();
        if($token === false){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        $db = DB::getDB();
        $user = $db->users->findOne([
            'access_token' => $token
        ],['_id','access_token','email','username','group_role','default_location','active_group']);
        
        if ($user !== null) {
            $user['id'] = $user['_id']->{'$id'};
            unset($user['_id']);
            
            self::$user_id = $user['id'];
            
            if (self::$group_role == null) {
                
                $active_role = false;
                foreach($user['group_role'] as $user_group){
                    if($user_group['group_id'] == $user['active_group']){
                        $active_role = $user_group['role_perm_id'];
                    }
                }
                
                $role_perm = $db->role_perm->findOne([
                    '_id' => new \MongoId($active_role)
                ]);
                
                self::$group_role = $role_perm['perms'];
            }
            
            if(!isset($user['default_location'])){
                self::$default_location = [];
            }else{
                self::$default_location = $user['default_location'];
            }
            
            return true;
        }
        
        return false;
    }
    
    public static function hasPermission($name, $action) {
        
        $token = UserHelper::check_token();
        if($token === false){
            throw new ServiceException(ResponseHelper::error('Invalid user token'));
        }
        
        $db = DB::getDB();
        $perm = $db->permission->findOne([
            'name' => $name,
            'action' => $action
        ]);
        if($perm === null){
            throw new ServiceException(ResponseHelper::error('Invalid permission'));
        }
        $perm_access = in_array($perm['_id']->{'$id'}, UserHelper::$group_role);
        return $perm_access;
    }
}