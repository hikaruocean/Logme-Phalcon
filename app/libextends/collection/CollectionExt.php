<?php

namespace Phalcon\Mvc\Collection;

class CollectionExt extends \Phalcon\Mvc\Collection {

    public static function mapReduce($db,$mapMongoCode, $reduceMongoCode, $tmpCollectionName) {
        if(!$db) return false;
        $collectionName = static::getSource();
        if (!$reduceMongoCode) {
            $reduceMongoCode = new \MongoCode("function(k, v) { " .
                    "var result = {};" .
                    "for (var i in v) {" .
                    "result[i]= v[i];" .
                    "}" .
                    "return result; }");
        } 
       $result = $db->command(array(
            "mapreduce" => $collectionName,
            "map" => $mapMongoCode,
            "reduce" => $reduceMongoCode,
            "out" => array("reduce" => $tmpCollectionName)));
        return $result['ok'] == 1 ? $result : false;
    }

    public function mapReduceFind($db,array $result, array $options = []) {
        if (!$result || !$db) {
            return [];
        }
        return $this->mongo->selectCollection($result['result'])->find($options);
    }

    public function mapReduceClose($db,array $result) {
        if (!$result || !$db) {
            return true;
        } else {
            $result = $this->mongo->selectCollection($result['result'])->drop();
            return $result['ok'] == 1 ? true : false;
        }
    }

}
