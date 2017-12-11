<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/4
 * Time: 上午10:10
 */

namespace app\Api\Controllers;


use App\Friend;
use App\Like;
use App\Note;
use App\Notebooks;
use App\Post;
use App\Tag;
use App\Fork;
use App\User;
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

    public function getNotesByUser(Request $request)
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

        $visitor = $request->only('user');
        $isFriend = $this->isFriend($user->name, $visitor['user']);
//        return $this->isFriend($user->name, $visitor['user']);

        if (!$isFriend) {
            $notebooks = Notebooks::where('user', $visitor['user'])
                ->where('authority', '所有人')
                ->distinct()
                ->get();
        } else {
            $notebooks = Notebooks::where('user', $visitor['user'])
                ->where('authority', '所有人')
                ->orWhere('authority', '仅好友')
                ->distinct()
                ->get();
        }

//        return response()->json(json_encode($notebooks, JSON_UNESCAPED_UNICODE));


        $notes = array();

        for ($count = 0; $count < count($notebooks); $count++) {
            if (!$isFriend) {
                $notesOfNotebook = Note::where([
                    ['notebook', $notebooks[$count]->id],
                    ['note_authority', '所有人'],
                    ['user', $visitor['user']]
                ])
                    ->distinct()
                    ->get()->toArray();
            } else {
                $notesOfNotebook = Note::where([
                    ['notebook', $notebooks[$count]->id],
                    ['note_authority', '所有人'],
                    ['user', $visitor['user']]
                ])
                    ->orWhere([
                        ['note_authority', '仅好友'],
                        ['notebook', $notebooks[$count]->id],
                        ['user', $visitor['user']]
                    ])
                    ->distinct()
                    ->get()->toArray();
            }
//            return gettype($notesOfNotebook);
//            $notesOfNotebook = (array)$notesOfNotebook;
            $notes = array_merge($notes, $notesOfNotebook);
        }


        return response()->json(json_encode($notes, JSON_UNESCAPED_UNICODE));

    }

    public function searchAll(Request $request)
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


        $users = User::where('name', 'like', '%' . $infos['contain'] . '%')
            ->orWhere('intro', 'like', '%' . $infos['contain'] . '%')
            ->distinct()
            ->limit(3)
            ->get();

        for ($i = 0; $i < count($users); $i++) {
//            $info = User::where('name', $users[$i]->user)->first();
            if ($this->isFriend($user->name, $users[$i]->name)) {
                $users[$i]['isFriend'] = "互相关注";
            } else {

                if (Friend::where([
                        ['user', $users[$i]->name],
                        ['follower', $user->name]
                    ])->count() > 0) {
                    $users[$i]['isFriend'] = "取消关注";
                } else {
                    $users[$i]['isFriend'] = "关注";
                }
            }

            if ($user->name == $users[$i]->name) {
                $users[$i]['isFriend'] = "我的信息";
            }
//            array_push($result, $info);
        }

        $notes = DB::table('notes')
            ->leftJoin('notetags', 'notes.id', '=', 'notetags.note_id')
            //            ->leftJoin('friends as f1', 'notes.user', '=', 'f1.user')
//            ->select('notes.id', 'f1.user', 'f1.follower as friend')
//            ->leftJoin('friends as f2', 'friend', '=', 'f2.user')
            ->where([
                ['notes.note_body', 'like', '%' . $infos['contain'] . '%'],
//                ['notes.note_authority', '=', '所有人']
            ])
            ->orWhere([
                ['notes.note_title', 'like', '%' . $infos['contain'] . '%'],
//                ['notes.note_authority', '=', '仅好友'],
//                ['f1.user', '=', 'f2.follower']
            ])
            ->orWhere([
                ['tag', 'like', '%' . $infos['contain'] . '%']
            ])
            ->select('notes.id', 'notes.user', 'notes.note_authority', 'notes.note_body', 'notes.updated_at', 'note_title')
//            ->orWhere([
//                ['notes.note_title', 'like', '%' . $infos['contain'] . '%' ],
//                ['notes.note_authority', '=', '所有人']
//            ])
//            ->orWhere([
//                ['notes.note_title', 'like', '%' . $infos['contain'] . '%' ],
//                ['notes.note_authority', '=', '仅好友'],
//                ['f1.user', '=', 'f2.follower']
//            ])
//            ->select('notes.id', 'f1.user', 'f1.follower as friend', 'f2.follower as me', 'notes.note_authority')

            ->orderBy('notes.updated_at', 'desc')
            ->distinct()
            //            ->whereExists(function ($query) use () {
//                $query->select(DB::raw(1))
//                    ->from('friends')
//                    ->whereRaw('orders.user_id = users.id');
//            })
            ->get();

        $posts = Post::where([
            ['content', 'like', '%' . $infos['contain'] . '%']
        ])
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();

        $note = array();

        for ($count = 0; $count < count($notes); $count++) {
            if ($notes[$count]->note_authority == '仅好友' && $user->name != $notes[$count]->user) {
                if (!$this->isFriend($user->name, $notes[$count]->user)) {
                    continue;
                }
            }
            if ($notes[$count]->note_authority == '只有我') {
                if ($user->name != $notes[$count]->user) {
                    continue;
                }
            }
            array_push($note, $notes[$count]);

            if (count($note) == 3) {
                break;
            }
        }


        $result = array($users, $note, $posts);


        return response()->json(json_encode($result, JSON_UNESCAPED_UNICODE));

    }

    public function getNoteSearchResult(Request $request)
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

        $notes = DB::table('notes')
            ->leftJoin('notetags', 'notes.id', '=', 'notetags.note_id')
            ->where([
                ['notes.note_body', 'like', '%' . $infos['contain'] . '%'],
            ])
            ->orWhere([
                ['notes.note_title', 'like', '%' . $infos['contain'] . '%'],
            ])
            ->orWhere([
                ['tag', 'like', '%' . $infos['contain'] . '%']
            ])
            ->select('notes.id', 'notes.user', 'notes.note_authority', 'notes.note_body', 'notes.updated_at', 'note_title')
            ->orderBy('notes.updated_at', 'desc')
            ->distinct()
            ->get();

        $note = array();

        for ($count = 0; $count < count($notes); $count++) {
            if ($notes[$count]->note_authority == '仅好友' && $user->name != $notes[$count]->user) {
                if (!$this->isFriend($user->name, $notes[$count]->user)) {
                    continue;
                }
            }
            if ($notes[$count]->note_authority == '只有我') {
                if ($user->name != $notes[$count]->user) {
                    continue;
                }
            }
            array_push($note, $notes[$count]);

        }

        return response()->json(json_encode($note, JSON_UNESCAPED_UNICODE));

    }

    public function searchInNotebook(Request $request)
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

        $infos = $request->only('notebook', 'contain');

        if ($infos['notebook'] == 0) {
            $notes = DB::table('notes')
                ->leftJoin('notetags', 'notes.id', '=', 'notetags.note_id')
                ->where([
                    ['user', $user->name],
                    ['note_title', 'like', '%' . $infos['contain'] . '%']
                ])
                ->orWhere([
                    ['user', $user->name],
                    ['note_body', 'like', '%' . $infos['contain'] . '%']
                ])
                ->orWhere([
                    ['user', $user->name],
                    ['tag', 'like', '%' . $infos['contain'] . '%']
                ])
                ->select('notes.id', 'notes.notebook', 'notes.note_title', 'notes.note_body', 'notes.note_authority', 'notes.updated_at')
                ->distinct()
                ->get()
                ->toArray();
        } else {
            $notes = DB::table('notes')
                ->leftJoin('notetags', 'notes.id', '=', 'notetags.note_id')
                ->where([
                    ['notebook', $infos['notebook']],
                    ['note_title', 'like', '%' . $infos['contain'] . '%']
                ])
                ->orWhere([
                    ['notebook', $infos['notebook']],
                    ['note_body', 'like', '%' . $infos['contain'] . '%']
                ])
                ->orWhere([
                    ['notebook', $infos['notebook']],
                    ['tag', 'like', '%' . $infos['contain'] . '%']
                ])
                ->select('notes.id', 'notes.notebook', 'notes.note_title', 'notes.note_body', 'notes.note_authority', 'notes.updated_at')
                ->distinct()
                ->get()
                ->toArray();
        }

        return response()->json(json_encode($notes, JSON_UNESCAPED_UNICODE));
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

}