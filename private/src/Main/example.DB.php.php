<?php

namespace Main;

class DB {
    /** @var  \MongoClient $mongo */
    private static $mongo, $db;
    public static function getMongo(){
        if(is_null(self::$mongo)){
            self::$mongo = new \MongoClient("127.0.0.1:27017");
        }
        return self::$mongo;
    }

    public static function getDB(){
        if(is_null(self::$db)){
            self::$db = self::getMongo()->your_database_name;
        }
        return self::$db;
    }
}