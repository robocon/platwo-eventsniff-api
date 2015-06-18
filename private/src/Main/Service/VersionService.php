<?php

namespace Main\Service;

use Main\Context\Context,
    Main\Exception\Service\ServiceException,
    Main\DB,
    Main\Helper\ResponseHelper;

class VersionService extends BaseService {


    public function get_last_version(Context $ctx){
        // $user = $ctx->getUser();
        // if(!$user){
        //     throw new ServiceException(ResponseHelper::error('Invalid token'));
        // }

        $db = DB::getDB();
        $items = $db->version->find()->sort(['_id' => -1])->limit(1);

        $res = [];
        foreach($items as $item){
            $res = $item;
        }

        unset($res['_id']);
        return $res;
    }

}
