<?php

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Service\VersionService;

/**
 * @Restful
 * @uri /version
 */
class VersionCTL extends BaseCTL {
    
    /**
     * @GET
     */
    public function get_last_version() {
        try {
            $items = VersionService::getInstance()->get_last_version($this->getCtx());
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
