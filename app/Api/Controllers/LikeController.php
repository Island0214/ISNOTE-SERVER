<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/4
 * Time: 下午12:28
 */

namespace app\Api\Controllers;

use App\Like;
use Dingo\Api\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;


class LikeController
{
    public function getLikeInfo($username, $note_id)
    {
        $like = Like::where([
            ['user', $username],
            ['note_id', $note_id]
        ]);
        if ($like) {
            return true;
        } else {
            return false;
        }
    }

    public function like(Request $request)
    {
        $like_info = $request->only('note_id');
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
        $like_info['user'] = $user->name;
        return Like::create($like_info);
    }

    public function cancelLike(Request $request)
    {
        $like_info = $request->only('note_id');
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
        $like = Like::where([
            ['user', $user->name],
            ['note_id', $like_info['note_id']]
        ])->delete();

        return $like;
    }
}