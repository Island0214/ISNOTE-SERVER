<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/4
 * Time: 下午1:59
 */

namespace app\Api\Controllers;
use App\Post;
use Dingo\Api\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class PostController
{
    public function getPostInfo(Request $request)
    {
//        $like_info = $request->only('note_id');
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => '获取信息失败']);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired']);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'token_absent']);
        }

    }

    public function sharePost(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => '获取信息失败']);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired']);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'token_absent']);
        }
        $post_info = $request->only('note_id', 'content');

        $post_info['user'] = $user->name;
        $post_info['type'] = 1;


        $post = Post::create($post_info);
        return response()->json(compact('post'));
    }

}