<?php
class RedisHelper {
    public static function isRedisAvailable($hostname = '127.0.0.1', $port = 6379) {
        if (!extension_loaded('redis')) {
            return false;
        }

        try {
            $redis = new Redis();
            $redis->connect($hostname, $port);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>