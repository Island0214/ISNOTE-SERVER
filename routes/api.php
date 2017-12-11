<?php


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
    $api->group(['namespace' => 'App\Api\Controllers'], function ($api) {
        $api->post('user/login', 'AuthController@authenticate');
        $api->post('user/register', 'AuthController@register');
        $api->post('user/modify', 'AuthController@modifyUser');
        $api->post('user/modifyPassword', 'AuthController@modifyPassword');

        $api->post('notebook/getNotebookById', 'NotebookController@getNotebookById');

        $api->post('note/uploadImage', 'NoteController@uploadImage');
        $api->get('note/getHotNotes', 'NoteController@getHotNotes');
        $api->post('post/getPostsByUser', 'PostController@getPostsByUser');


        $api->group(['middleware' => 'jwt.auth'], function ($api) {
            $api->get('user/me', 'AuthController@getAuthenticatedUser');

            $api->post('notebook/createNotebook', 'NotebookController@createNotebook');
            $api->post('notebook/modifyNotebook', 'NotebookController@modifyNotebook');
            $api->get('notebook/getAllNotebooksByUser', 'NotebookController@getAllNotebooksByUser');

            $api->get('note/getAllNotesByUser', 'NoteController@getAllNotesByUser');
            $api->post('note/createNote', 'NoteController@createNote');
            $api->post('note/modifyNote', 'NoteController@modifyNote');
            $api->post('note/forkNote', 'NoteController@forkNote');
            $api->post('note/deleteNote', 'NoteController@deleteNote');
            $api->post('note/getNotesByNotebook', 'NoteController@getNotesByNotebook');
            $api->post('note/getNoteById', 'NoteController@getNoteById');
            $api->post('note/getNotesByUser', 'NoteController@getNotesByUser');
            $api->post('note/searchInNotebook', 'NoteController@searchInNotebook');
            $api->post('note/searchAll', 'NoteController@searchAll');

            $api->post('like/likeNote', 'LikeController@like');
            $api->post('like/cancelLike', 'LikeController@cancelLike');

            $api->post('tag/addTag', 'TagController@addTag');
            $api->post('tag/deleteTag', 'TagController@deleteTag');

            $api->post('post/sharePost', 'PostController@sharePost');
            $api->post('post/sendPost', 'PostController@sendPost');
            $api->get('post/getPostsOfMyFollowing', 'PostController@getPostsOfMyFollowing');

            $api->get('friend/getOneRecommendation', 'FriendController@getOneRecommendation');
            $api->get('friend/getMyFollowers', 'FriendController@getMyFollowers');
            $api->get('friend/getMyFollowing', 'FriendController@getMyFollowing');
            $api->post('friend/followUser', 'FriendController@followUser');
            $api->post('friend/cancelFollow', 'FriendController@cancelFollow');
            $api->post('friend/getFriendByName', 'FriendController@getFriendByName');
        });
    });
});

