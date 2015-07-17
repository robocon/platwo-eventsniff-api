<?php

namespace Main\CTL;

use Main\Service\MapService,
    Main\Exception\Service\ServiceException,
    Main\Helper\ResponseHelper,
    Main\Helper\UserHelper;

/**
 * @Restful
 * @uri /map
 */
class MapCTL extends BaseCTL {
    
    /**
     * @POST
     * @uri /minimap
     */
    public function minimap() {
        try {
            $items = MapService::getInstance()->minimap($this->reqInfo->params(), $this->getCtx());
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
}
