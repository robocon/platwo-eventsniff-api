<?php

namespace Main\Service;
use Main\Context\Context,
    Main\DB,
    Main\Exception\Service\ServiceException,
    Main\Helper\ResponseHelper,
    Main\Helper\UserHelper,
    Valitron\Validator;

/**
 * Description of LogService
 *
 * @author robocon
 */
class LogService extends BaseService {
    
    private function getLogCollection(){
        $db = DB::getDB();
        return $db->logs;
    }
    
    public function save($token, $params, Context $ctx) {
        
        if(UserHelper::check_token($token) === false){
            throw new ServiceException(ResponseHelper::error('Invalid user'));
        }
        
        // Get user id from token
        $user_id = UserHelper::$user_id;
        $params['user_id'] = $user_id;
        
        $v = new Validator($params);
        $v->rule('required', ["type", "status", "user_id", "reference_id"]);
        if(!$v->validate()) {
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $params['log_date'] = $birth_date = new \MongoDate();
        
        $db = $this->getLogCollection()->insert($params);
        if($db['n'] === 0){
            return true;
        }
        return false;
    }
}
