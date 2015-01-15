<?php
/**
 * Created by PhpStorm.
 * User: robocon
 * Date: 1/10/15
 * Time: 12:37 PM
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException;
use Main\Service\EventService;
use Main\Service\GalleryService;

/**
 * Class EventCTL
 * @package Main\CTL
 * @Restful
 * @uri /gallery
 */
class GalleryCTL extends BaseCTL {

    /**
     * @POST
     */
    public function add(){
        try{     

            $items = GalleryService::getInstance()->add($this->reqInfo->params(), $this->getCtx());
            return $items;

        } catch(ServiceException $e) {
            return $e->getResponse();
        }
    }
}