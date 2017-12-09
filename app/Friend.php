<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/8
 * Time: 下午2:47
 */

namespace app;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class Friend extends Model implements Authenticatable
{
    use AuthenticableTrait;

    protected $table = 'friends';

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
        'user', 'follower'
    ];
}