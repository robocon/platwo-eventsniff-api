<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 10/6/14
 * Time: 11:24 AM
 */

namespace Main\Helper;


class UserHelper {
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
    
    public static function generate_key() {
        return hash('sha256', time().uniqid().SITE_BLOWFISH);
    }
    
    public static function generate_token($user_id, $user_private_key){
        return hash('sha256', $user_id.uniqid().$user_private_key.uniqid());
    }
    
    public static function generate_password($password, $user_private_key){
        return hash('sha256', $password.SITE_BLOWFISH.$user_private_key);
    }
}