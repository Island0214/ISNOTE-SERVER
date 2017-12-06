<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/4
 * Time: 下午1:23
 */

namespace app;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class Like extends Model implements Authenticatable
{
    use AuthenticableTrait;

    protected $table = 'likes';

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
        'user', 'note_id'
    ];
}