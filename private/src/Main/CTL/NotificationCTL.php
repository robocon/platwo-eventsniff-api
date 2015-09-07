<?php

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Service\NotificationService;

/**
 * @Restful
 * @uri /notification
 */
class NotificationCTL extends BaseCTL {
    
    /**
     * Show all notification 
     * 
     * @GET
     * @uri /all
     */
    public function get_notifications(){
        
        /**
         * @todo Notificaton
         * [x] Alarm (Event alarm)
         * [x] Report from event
         * 
         * Web admin don't do this feature in this time
         * [] Update event
         * [] Approve event from web admin
         */
        try {
            $items = NotificationService::getInstance()->get_notifications($this->getCtx());
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * Show only report event and picture
     * 
     * @GET
     * @uri /reports
     */
    public function reports() {
        try {
            $items = NotificationService::getInstance()->get_reports($this->getCtx());
            return $items;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * 
     * @GET
     * @uri /report/[h:report_id]
     */
    public function report_detail() {
        try {
            $item = NotificationService::getInstance()->get_report($this->reqInfo->urlParam('report_id'), $this->getCtx());
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
