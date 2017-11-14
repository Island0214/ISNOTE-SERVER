<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/11/14
 * Time: 下午3:16
 */

namespace App\Api\Controllers;
use App\Api\Transformer\PostTransformer;

class PostsController
{
    public function index()
    {
        $lessons =  Post::all();

        return $this->collection($post,new PostTransformer());
    }

    public function show($id)
    {
        $lesson = Lesson::find($id);
        if(! $lesson){
            return $this->response->errorNotFound('Lesson not found');
        }
        return $this->item($lesson,new LessonTransformer());
    }
}