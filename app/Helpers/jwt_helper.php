<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!function_exists('generateJWT')) {
    function generateJWT($data)
    {
        $key = getenv('JWT_SECRET');
        return JWT::encode($data, $key, 'HS256');
    }
}

if (!function_exists('decodeJWT')) {
    function decodeJWT($token)
    {
        $key = getenv('JWT_SECRET');
        try {
            return JWT::decode($token, new Key($key, 'HS256'));
        } catch (Exception $e) {
            return null;
        }
    }
}
