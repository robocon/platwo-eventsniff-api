<?php

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Service\WebadminService;

/**
 * @Restful
 * @uri /webadmin
 */
class WebadminCTL extends BaseCTL {
    //put your code here
    
    /**
     * Send notification after approve
     * 
     * @POST
     * @uri /approve
     */
    public function approve() {
        try {
            $item = WebadminService::getInstance()->approve($this->reqInfo->params(), $this->getCtx());
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
