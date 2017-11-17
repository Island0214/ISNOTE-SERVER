<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/11/17
 * Time: 下午3:19
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notebook extends Model
{
    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'connection-name';
}
