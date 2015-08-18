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
        
//        $category = null;
//        if (is_array($params['category'])) {
//            $category = $params['category'];
//        }
        
        $db = DB::getDB();
        list($lng, $lat) = explode(',', $params['location']);
        
        $max_distance = 10 / 6378.1;
        
        $where = [
            'build' => 1,
            'approve' => 1,
            '$and' => [
                ['location' => [
                    '$geoWithin' => [
                        '$centerSphere' => [ [(float)$lng, (float)$lat], $max_distance]
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
        ];
        
        if (is_array($params['category'])) {
            $where['categories'] = ['$in' => $params['category']];
        }
        
        $items = $db->event->find($where,['name','date_start','date_end','picture','location','sniffer','categories'])->sort(['date_start' => 1]);
        
        $event_lists = [];
        $user_id = $user['_id']->{'$id'};
        foreach($items as $item){
            
            $item['id'] = $item['_id']->{'$id'};
            unset($item['_id']);
            
            // Default active to false
            $item['active'] = false;
            $test_date_end = $item['date_end']->sec;
            $test_date_start = $item['date_start']->sec;
            if ( $test_date_start <= $set_time && $test_date_end >= $set_time ) {
                $item['active'] = true;
            }
            
            unset($item['date_end']);
            
            $item['date_start'] = MongoHelper::dateToYmd($item['date_start']);
            
            $picture = $db->gallery->findOne(['event_id' => $item['id']],['picture']);
            $item['thumb'] = Image::load_picture($picture['picture']);
                
            $item['total_sniffer'] = $db->sniffer->find(['event_id' => $item['id']])->count();
            
            $location = $db->location->findOne(['event_id' => $item['id']],['position']);
            $item['location'] = $location['position'];
            
//            if($category !== null){
//                
//                $tag_count = $db->event_tag->find([
//                    'tag_id' => ['$in' => $category],
//                    'event_id' => $item['id']
//                ],['event_id'])->count();
//                
//                if($tag_count === 0){
//                    continue;
//                }
//            }
            $item['sniffed'] = 'false';
            if(in_array($user_id, $item['sniffer'])){
                $item['sniffed'] = 'true';
            }
            unset($item['sniffer']);
            
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

### Calculate Distance Using Spherical Geometry
### http://docs.mongodb.org/manual/tutorial/calculate-distances-using-spherical-geometry-with-2d-geospatial-indexes/
db.event.find( { "location": 
    { $geoWithin: 
        { $centerSphere: [ 
            [ 98.9733419,18.7977019 ] ,
            10 / 6378.1 ] 
        } 
    } 
},{"name":1, "location":1})
         */
        
    }
}
