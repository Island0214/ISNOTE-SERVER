<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/6
 * Time: 下午7:44
 */

namespace app;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;


class Tag extends Model implements Authenticatable
{
    use AuthenticableTrait;

    protected $table = 'notetags';

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
        'note_id', 'tag'
    ];
}