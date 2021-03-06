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
    
    /**
     * @GET
     * @uri /[h:category_id]
     */
    public function get_events(){
        try {
            $item = CategoryService::getInstance()->get_events($this->reqInfo->urlParam('category_id'), $this->getCtx());
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @POST
     * @uri /search
     */
    public function search_category() {
        try {
            $item = CategoryService::getInstance()->search_category($this->reqInfo->params(), $this->getCtx());
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
    
    /**
     * @GET
     * @uri /now/[h:category_id]
     */
    public function now() {
        try {
            $item = CategoryService::getInstance()->now($this->reqInfo->param('category_id'), $this->getCtx());
            return $item;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
