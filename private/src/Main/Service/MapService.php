<?php

namespace Main\Service;
use Main\Context\Context,
    Main\DB,
    Main\DataModel\Image,
    Main\Exception\Service\ServiceException,
    Main\Helper\ResponseHelper,
    Main\Helper\UserHelper,
    Main\Helper\MongoHelper,
    Valitron\Validator;

class MapService extends BaseService{
    
    public function minimap($params, Context $ctx) {
        
        $user = $ctx->getUser();
        if(!$user){
            throw new ServiceException(ResponseHelper::error('Invalid token'));
        }
        
        $v = new Validator($params);
        $v->rule('required', ['category', 'location']);
        if(!$v->validate()){
            throw new ServiceException(ResponseHelper::validateError($v->errors()));
        }
        
        $date = new \DateTime();
        $set_time = $date->getTimestamp();
        $now = new \MongoDate($set_time);
        
        $category = null;
        if (is_array($params['category'])) {
            $category = $params['category'];
        }
        
        $db = DB::getDB();
        list($lng, $lat) = explode(',', $params['location']);
        
        $items = $db->event->find([
            'build' => 1,
            'approve' => 1,
            '$and' => [
                ['location' => [
                    '$geoWithin' => [
                        '$center' => [ [(float)$lng, (float)$lat], 20000]
                    ]
                ]],
                ['$or' => [
                    ['date_start' => ['$gte' => $now]],
                    [
                        '$and' => [
                            ['date_start' => ['$lte' => $now]],
                            ['date_end' => ['$gte' => $now]]
                        ]
                    ]
                ]]
            ]
            
        ],['name','date_start','picture','location'])->sort(['date_start' => 1]);
        
        $event_lists = [];
        foreach($items as $item){
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);

            $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
            
            $picture = $db->gallery->findOne(['event_id' => $item['id']],['picture']);
            $item['thumb'] = Image::load_picture($picture['picture']);
                
            $item['total_sniffer'] = $db->sniffer->find(['event_id' => $item['id']])->count();
            
            $location = $db->location->findOne(['event_id' => $item['id']],['position']);
            $item['location'] = $location['position'];
            
            if($category !== null){
                
                $tag_count = $db->event_tag->find([
                    'tag_id' => ['$in' => $category],
                    'event_id' => $item['id']
                ],['event_id'])->count();
                
                if($tag_count === 0){
                    continue;
                }
            }
            $event_lists[] = $item;
        }
        
        $res = ['data' => $event_lists, 'length' => count($event_lists)];
        return $res;
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
