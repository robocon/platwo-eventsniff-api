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
     * @uri minimap
     */
    public function minimap() {
        try {
            $item = MapService::getInstance()->minimap($this->reqInfo->params(), $this->getCtx());
            return ['success' => $item];
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
}
