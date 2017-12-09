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
use App\Fork;
use Dingo\Api\Contract\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $credentials['note_body'] = '';

        $note = Note::create($credentials);

        if ($note->note_authority == '所有人') {
            $post = array(
                'user' => $note->user,
                'note_id' => $note->id,
                'type' => 2
            );
            Post::create($post);
        }
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
        $note = Note::where([
            ['id', $credentials['id']]
        ])->first();

        if ($note->note_authority == '所有人') {
            $hasPost = Post::where([
                ['note_id', $note->id],
                ['type', 3]
            ])->count();
            if ($hasPost > 0) {
                $post = Post::where([
                    ['note_id', $note->id],
                    ['type', 3]
                ])->delete();
            }
            $post = array(
                'user' => $user->name,
                'note_id' => $note->id,
                'type' => 3
            );
            Post::create($post);
        }
        return response()->json(compact('note'));
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
                $note['fork_count'] = Fork::where('from_note_id', $infos['id'])->count();;

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

    public function forkNote(Request $request)
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

        $infos = $request->only('notebook', 'authority', 'note');

        $from_note = Note::where('id', $infos['note'])->first();

        $note_info = array(
            "user" => $user->name,
            "notebook" => $infos['notebook'],
            "note_title" => $from_note->note_title,
            "note_body" => $from_note->note_body,
            "note_authority" => $infos['authority'],
        );
        $to_note = Note::create($note_info);

        $fork_info = array(
            "from_note_id" => $from_note->id,
            "to_note_id" => $to_note->id,
        );

        $fork = Fork::create($fork_info);

        return response()->json(compact('fork'));

    }

    public function getHotNotes()
    {
        $allNotes = DB::table('notes')
            ->leftJoin('likes', 'notes.id', '=', 'likes.note_id')
            ->leftJoin('forks', 'notes.id', '=', 'forks.from_note_id')
            ->leftJoin('reposts', 'notes.id', '=', 'reposts.note_id')
            ->select(DB::raw('notes.*, count(*) as count'))
            ->where('note_authority', '所有人')
            ->groupBy('notes.id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
//        $note['like_count'] = Like::where('note_id', $infos['id'])->count();;
//        $note['post_count'] = Post::where('note_id', $infos['id'])->count();;
//        $note['fork_count'] = Fork::where('from_note_id', $infos['id'])->count();;
//        $hotNotesID = array();
//        for ($x = 0; $x < count($allNotes); $x++) {
//            array_push($hotNotesID, $allNotes[$x]);
//            array_sort($hotNotesID);
//        }
        return response()->json(json_encode($allNotes, JSON_UNESCAPED_UNICODE));
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