<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/11/15
 * Time: 上午11:45
 */

namespace App\Api\Controllers;


use App\User;
use Dingo\Api\Contract\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    public function authenticate(Request $request)
    {
//        // grab credentials from the request
        $credentials = $request->only('name', 'password');
        try {
//            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => '用户名不存在或密码错误']);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => '登录失败']);
        }
        // all good so return the token
        return response()->json(compact('token'));
    }

    public function register(Request $request)
    {
        $credentials = $request->only('name', 'password');
        $registerInfo = $request->only('name', 'password');
        $registerInfo["email"] = "";
        $registerInfo["phone"] = "";
        $registerInfo["gender"] = "";
        $registerInfo["icon"] = "";
        $registerInfo["intro"] = "";
        $registerInfo["see"] = "所有人";
        $registerInfo["search"] = "所有人";
        $registerInfo["info"] = "所有人";
        $registerInfo["modify"] = "所有人";

//        echo "======================\n";
//        echo "register\n";
//        echo "name: {$registerInfo['name']}\n";
//        echo "password:  {$registerInfo['password']}\n";
        if (strlen($registerInfo["name"]) == 0){
            return response()->json(['error' => '用户名不能为空！']);
        }
        if(strlen($registerInfo["password"]) < 6){
            return response()->json(['error' => '密码必须大于六位！']);
        }

        $registerInfo["password"] = Hash::make($registerInfo["password"]);
        if (User::where('name', $registerInfo["name"])->count() > 0) {
            return response()->json(['error' => '该用户名已被注册！']);
        } else {
            User::create($registerInfo);
            try {
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json(['error' => '注册失败']);
                }
            } catch (JWTException $e) {
                // something went wrong whilst attempting to encode the token
                return response()->json(['error' => '注册失败']);
            }
            // all good so return the token
            return response()->json(compact('token'));
        }
    }

    public function getAuthenticatedUser()
    {

        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }

    public function modifyUser()
    {

    }
}