<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/11/13
 * Time: 下午7:53
 */
return [
    'auth' => [
        'basic'=>function($app){
            return new  Dingo\Api\Auth\Provider\Basic($app['auth']);
        },
        'jwt'=>function($app){
            return new  Dingo\Api\Auth\Provider\JWT($app['Tymon\JWTAuth\JWTAuth']);
        }
    ],
];