<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Main\CTL;

use Main\Exception\Service\ServiceException;
use Main\Service\SniffService;

/**
 * @Restful
 * @uri /sniff
 */
class SniffCTL extends BaseCTL {
    /**
     * @GET
     */
    public function gets() {
        
        return array('asdf' => 'asdfasf');
    }
    
    /**
     * @api {get} /sniff/category GET /sniff/category
     * @apiDescription Get all category
     * @apiName SniffCategory
     * @apiGroup Sniff
     * @apiSuccessExample {json} Success-Response:
     * {
     *      "length": 19,
     *      "data": [
     *          {
     *              "id": "6f2da37e72bf9e100b40567c",
     *              "name" "Awards",
     *          },
     *          {
     *              "id": "e9c5c932c205770da433d3de",
     *              "name" "Conferences",
     *          },
     *          ...
     *      ]
     * }
     * 
     * @GET
     * @uri /category
     */
    public function category() {
        try {
            $categories = SniffService::getInstance()->get(array(), $this->getCtx());
            return $categories;
        } catch (ServiceException $e) {
            return $e->getResponse();
        }
    }
}
