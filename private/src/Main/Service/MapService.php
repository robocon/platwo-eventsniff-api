<?php

namespace Main\Service;
use Main\Context\Context,
    Main\DB,
    Main\Exception\Service\ServiceException,
    Main\Helper\ResponseHelper,
    Main\Helper\UserHelper,
    Valitron\Validator;

class MapService extends BaseService{
    
    public function minimap($params) {
        
        $v = new Validator($params);
        $v->rule('required', ['category', 'location']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $date = new \DateTime();
        $set_time = $date->getTimestamp();
        $current_time = new \MongoDate($set_time);
        
        $category = null;
        if (is_array($params['category'])) {
            $category = $params['category'];
        }
        /**
         * If category not all
         * find an event in event_tag befor event
         * 
         * if all
         * find from location
         * 
         * @todo find active + inactive event
         */
        /*
### $geoWithin
db.event.find({"location": 
    {"$geoWithin": 
        {"$center": [[ 98.987160, 18.788342 ], 20000] } 
    } 
})

### $near
db.event.createIndex( { location : "2dsphere" } );
db.event.find({
    "location": {
        "$near": {
            "$geometry": { type: "Point",  coordinates: [ 98.98527145, 18.78842372 ] },
            $minDistance: 100,
            $maxDistance: 20000
        }
    }
});
         */
    }
}
