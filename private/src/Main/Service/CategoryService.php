<?php

namespace Main\Service;

use Main\DB,
        Main\Helper\ResponseHelper,
    Main\Context\Context;

/**
 * Description of CategoryService
 *
 * @author robocon
 */
class CategoryService extends BaseService {
//    public $db = null;
//    public function __construct() {
//        
//    }
    
    private function connect(){
        $db = DB::getDB();
        return $db;
    }
    
    public function sniff_category($params, Context $ctx){
        
        $user = $ctx->getUser();
        if($user === null){
            return ResponseHelper::error('Access denied');
        }
        
        $status = (bool) $params['status'];
        
        $db = $this->connect();
        if($status == true){
            
            // add into sniff_category
            $db->users->update(['_id' => $user['_id']],['$addToSet' => ['sniff_category' => $params['category_id']]]);
            
        }else{
            
            // remove from sniff_category
            $db->users->update(['_id' => $user['_id']],['$pull' => ['sniff_category' => $params['category_id']]]);
            
        }
        
        return ['success' => true];
    }
}
