<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/4
 * Time: 上午10:10
 */

namespace app\Api\Controllers;


use App\Like;
use App\Note;
use App\Notebooks;
use App\Post;
use App\Tag;
use Dingo\Api\Contract\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;


class NoteController
{
    public function createNote(Request $request)
    {
        $credentials = $request->only('notebook', 'note_title', 'note_authority');


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
        $credentials['user'] = $user->name;
//        $noteInfos['notebook'] = $credentials['notebook_id'];
//        $noteInfos['note_title'] = $credentials['name'];
//        $noteInfos['note_authority'] = $credentials['authority'];
        $credentials['note_body'] = '<p></p>';

        $note = Note::create($credentials);
        return response()->json(compact('note'));
    }

    public function deleteNote(Request $request)
    {
        $noteID = $request->only('id');
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

        Note::where('id', $noteID)->delete();
        return response()->json(['success' => '成功删除笔记！']);
    }

    public function modifyNote(Request $request)
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

        $credentials = $request->only('id', 'title', 'body', 'authority');
        $note = Note::where([
            ['id', $credentials['id']]
        ])->first();
        $note->note_title = $credentials['title'];
        $note->note_body = $credentials['body'];
        $note->note_authority = $credentials['authority'];
        $note->save();
    }

    public function getNotesByNotebook(Request $request)
    {
        $notebook_id = $request->only('id');
        $notes = Note::where('notebook', $notebook_id)->get();
        return response()->json(json_encode($notes, JSON_UNESCAPED_UNICODE));
    }

    public function getAllNotesByUser()
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
        $notes = Note::where('user', $user->name)->get();
        return response()->json(json_encode($notes, JSON_UNESCAPED_UNICODE));
    }

    public function getNoteById(Request $request)
    {
        $infos = $request->only('id');

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

        $note = Note::where('id', $infos['id'])->first();
        if (!$note) {
            return response()->json(['error' => '获取笔记信息失败']);
        } else {
            $notebook = Notebooks::where('id', $note->notebook)->first();
            if (!$notebook) {
                return response()->json(['error' => '获取笔记信息失败']);
            } else {
                $note['notebook_name'] = $notebook->notebook_name;

                $isLike = false;
                $like = Like::where([
                    ['user', $user->name],
                    ['note_id', $infos['id']]
                ])->first();
                if ($like) {
                    $isLike = true;
                }
                $note['isLike'] = $isLike;

                $note['like_count'] = Like::where('note_id', $infos['id'])->count();;
                $note['post_count'] = Post::where('note_id', $infos['id'])->count();;
                $note['tags'] = Tag::where('note_id', $infos['id'])->get();

                return response()->json(compact('note'));
            }
        }
    }

    public function uploadImage(Request $request)
    {
        if (!empty($_FILES['image'])) {
            $file = $_FILES['image'];
            $path = $request->file('image')->store('public');
            $array = explode("/", $path);

            $url = $path;
            return response()->json(compact('url'));

        } else {
            return response()->json(['error' => '文件不存在！']);
        }

    }

    public function getNotesByNotebookAndAuthority(Request $request)
    {

    }

    public function searchAll(Request $request)
    {

    }

    public function searchInside(Request $request)
    {

    }
}