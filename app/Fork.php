<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/8
 * Time: 上午8:33
 */

namespace app;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class Fork extends Model implements Authenticatable
{
    use AuthenticableTrait;

    protected $table = 'forks';

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
        'from_note_id', 'to_note_id'
    ];
}