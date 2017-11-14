<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/11/14
 * Time: 下午3:18
 */
namespace App\Api\Transformer;
use App\Post;
use League\Fractal\TransformerAbstract;
class PostTransformer extends TransformerAbstract
{
    public function transform(Post $post)
    {
        return [
            'title' => $post['title'],
            'content' => $post['body'],
            'is_free' => (boolean)$ppost['free']
        ];
    }
}