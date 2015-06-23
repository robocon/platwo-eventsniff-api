<?php

namespace Main\CTL;

use Main\Exception\Service\ServiceException,
    Main\Helper\MongoHelper,
    Main\Service\CategoryService;

/**
 * @Restful
 * @uri /category
 */
class CategoryCTL extends BaseCTL{
    
    /**
     * Update When user sniff and unsniff category
     * - category_id
     * - status: true|false
     * - token
     * 
     * @PUT
     * @uri /sniff
     */
    public function sniff_category(){
        try {
            $params = $this->reqInfo->params();
            $item = CategoryService::getInstance()->sniff_category($params, $this->getCtx());
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @GET
     */
    public function get_categorys() {
        try {
            $item = CategoryService::getInstance()->get_categorys($this->getCtx());
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
