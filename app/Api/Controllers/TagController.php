<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/6
 * Time: 下午7:44
 */

namespace app\Api\Controllers;

use App\Tag;
use Dingo\Api\Contract\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class TagController
{
    public function addTag(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => '获取用户信息失败']);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired']);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'token_absent']);
        }

        $tagInfos = $request->only('note_id', 'tag');
        $tag = Tag::create($tagInfos);
        return response()->json(compact('tag'));
    }

    public function deleteTag(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => '获取用户信息失败']);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired']);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'token_absent']);
        }

        $tagInfos = $request->only('id');
        Tag::where('id', $tagInfos['id'])->delete();
        return response()->json(['success' => '删除成功!']);
    }
}