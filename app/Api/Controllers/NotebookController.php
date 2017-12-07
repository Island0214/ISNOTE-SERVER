<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/5
 * Time: 下午5:39
 */

namespace app\Api\Controllers;

use App\Notebooks;
use Dingo\Api\Contract\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotebookController
{
    /**
     * 创建新笔记本
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNotebook(Request $request)
    {
        $credentials = $request->only('name', 'authority');

        if (Notebooks::where('notebook_name', $credentials["name"])->count() > 0) {
            return response()->json(['error' => '已有重名笔记本！']);
        } else {
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
            $noteInfos['user'] = $user['name'];
            $noteInfos['notebook_name'] = $credentials['name'];
            $noteInfos['authority'] = $credentials['authority'];

            $notebook = Notebooks::create($noteInfos);
            return response()->json(compact('notebook'));
        }
    }

    /**
     * 获得用户自己的笔记本列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllNotebooksByUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => '获取信息失败']);
            } else {
                $notebooks = Notebooks::where('user', $user->name)->get();
                return response()->json(json_encode($notebooks, JSON_UNESCAPED_UNICODE));
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'token_expired']);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'token_invalid']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'token_absent']);
        }
    }

    /**
     * 根据笔记本id获得笔记本
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotebookById(Request $request){
        $notebook_id = $request->only('id');
        $notebook = Notebooks::where('id', $notebook_id)->first();
        return response()->json(compact('notebook'));

    }

    /**
     * 修改笔记本设置
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function modifyNotebook(Request $request)
    {
        $credentials = $request->only('name', 'authority', 'id');
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

        $notebook = Notebooks::where([
            ['id', $credentials['id']],
            ['user', $user->name]
        ])->first();
        $notebook->notebook_name = $credentials['name'];
        $notebook->authority = $credentials['authority'];
        $notebook->save();
        return response()->json(compact('notebook'));

    }


}