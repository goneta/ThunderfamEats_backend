<?php
require 'php-jwt/vendor/autoload.php';
use EllipticCurve\PublicKey;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTwrapper {

    public static function encode($data=''){
        return JWT::encode($data, CRON_KEY, 'HS256');
    }

    public static function decode($data=''){        
        return JWT::decode($data, new Key(CRON_KEY, 'HS256'));
    }
}
// end class