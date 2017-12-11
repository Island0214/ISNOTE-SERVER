<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/4
 * Time: 下午1:59
 */

namespace app\Api\Controllers;

use App\Note;
use App\Post;
use App\Friend;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function sendPost(Request $request)
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
        $post_info = $request->only('content');

        $post_info['user'] = $user->name;
        $post_info['type'] = 4;
        $post_info['note_id'] = 0;


        $post = Post::create($post_info);
        return response()->json(compact('post'));
    }

    public function getPostsByUser(Request $request)
    {
        $user = $request->only('user');

        $posts = Post::where(
            'user', $user['user']
        )
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json(json_encode($posts, JSON_UNESCAPED_UNICODE));
    }

    public function getPostsOfMyFollowing()
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

        $followings = Friend::where('follower', $user->name)->select('user')->get();

        $posts = DB::table('reposts')
            ->leftJoin('friends', 'reposts.user', '=', 'friends.user')
            ->where('friends.follower', $user->name)
            ->orWhere(function ($query) use ($user) {
                $query->where('reposts.user', $user->name);
            })
            ->select('reposts.user', 'note_id', 'reposts.updated_at', 'type', 'content')
            ->orderBy('reposts.updated_at', 'desc')
            ->limit(15)
            ->get();

//        for ($i = 0; $i < count($posts); $i++) {
//            if ($posts[$i]->note_id != 0) {
//                $posts[$i]['note'] = Note::where('id', $posts[$i]->note_id)->first();
//            }
//        }

//        $post
        return response()->json(json_encode($posts, JSON_UNESCAPED_UNICODE));
    }

    public function getPostSearchResult(Request $request)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => '获取信息失败']);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => '获取信息失败']);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => '获取信息失败']);
        } catch (JWTException $e) {
            return response()->json(['error' => '获取信息失败']);
        }

        $infos = $request->only('contain');

        $posts = Post::where([
            ['content', 'like', '%' . $infos['contain'] . '%']
        ])
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json(json_encode($posts, JSON_UNESCAPED_UNICODE));

    }

}