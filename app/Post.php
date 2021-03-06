<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/4
 * Time: 下午2:03
 */

namespace app;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;


class Post extends Model implements Authenticatable
{
    use AuthenticableTrait;

    protected $table = 'reposts';

    /**
     * 与模型关联的数据表
     *
     * @var string
     */
//    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        //type:1 笔记分享
        //type:2 笔记创建
        //type:3 笔记更新 ？
        //type:4 发表动态
        'user', 'note_id', 'type', 'content'
    ];
}