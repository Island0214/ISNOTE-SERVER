<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/8
 * Time: 下午1:27
 */

namespace app\Api\Controllers;

use App\Friend;
use App\User;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class FriendController
{
    public function followUser(Request $request)
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

        $followUser = $request->only('user');
        $followUser['follower'] = $user->name;
        $followInfo = Friend::where([
            ['user', $followUser['user']],
            ['follower', $user->name]
        ])->count();
        if ($followInfo > 0) {
            return response()->json(["success" => "已关注该用户"]);
        } else {
            $create = Friend::create($followUser);
            if ($create)
                return response()->json(["success" => "关注成功"]);
            else
                return response()->json(["error" => "关注失败，请重试..."]);
        }
    }

    public function cancelFollow(Request $request)
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

        $followUser = $request->only('user');
        $delete = Friend::where([
            ['user', $followUser['user']],
            ['follower', $user->name]
        ])->delete();
        if ($delete)
            return response()->json(["success" => "取消关注成功"]);
        else
            return response()->json(["error" => "取消关注失败，请重试..."]);

    }

    public function getOneRecommendation()
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

        $count = 0;

        do {
            $recommend = DB::table('users')
                ->where('id', '!=', $user->id)
                ->inRandomOrder()
                ->first();

            $friend = Friend::where([
                ['user', $recommend->name],
                ['follower', $user->name]
            ])
                ->count();

            $count++;
        } while ($friend > 0);

        return response()->json(json_encode($recommend, JSON_UNESCAPED_UNICODE));
    }

    public function getMyFollowers()
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

        $followers = Friend::where('user', $user->name)->get();
        $result = array();
        for ($i = 0; $i < count($followers); $i++) {
            $info = User::where('name', $followers[$i]->follower)->first();
            if ($this->isFriend($user->name, $followers[$i]->follower)) {
                $info['isFriend'] = "互相关注";
            } else {
                $info['isFriend'] = "关注";
            }
            array_push($result, $info);
        }
        return response()->json(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    public function getMyFollowing()
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

        $followers = Friend::where('follower', $user->name)->get();
        $result = array();
        for ($i = 0; $i < count($followers); $i++) {
            $info = User::where('name', $followers[$i]->user)->first();
            if ($this->isFriend($user->name, $followers[$i]->user)) {
                $info['isFriend'] = "互相关注";
            } else {
                $info['isFriend'] = "取消关注";
            }
            array_push($result, $info);
        }
        return response()->json(json_encode($result, JSON_UNESCAPED_UNICODE));
    }

    public function isFriend($user1, $user2)
    {
        $friend1 = Friend::where([
            ['user', $user1],
            ['follower', $user2]
        ])->count();

        $friend2 = Friend::where([
            ['user', $user2],
            ['follower', $user1]
        ])->count();

        if ($friend1 > 0 && $friend2 > 0)
            return true;
        else
            return false;
    }

    public function getFriendByName(Request $request)
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

        $info = $request->only('user');
        $friend = User::where('name', $info['user'])->first();

        if ($this->isFriend($user->name, $info['user'])) {
            $friend['isFriend'] = "互相关注";
        } else {

            if (Friend::where([
                    ['user', $info['user']],
                    ['follower', $user->name]
                ])->count() > 0) {
                $friend['isFriend'] = "取消关注";
            } else {
                $friend['isFriend'] = "关注";
            }
        }

        $friend['follower_count'] = Friend::where('user', $info['user'])->count();
        $friend['following_count'] = Friend::where('follower', $info['user'])->count();

        return response()->json(compact('friend'));
    }
}