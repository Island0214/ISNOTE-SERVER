<?php
/**
 * Created by PhpStorm.
 * User: island
 * Date: 2017/12/7
 * Time: 下午3:53
 */

namespace app\Util;
use Illuminate\Support\Facades\Request;


class ImageRoute
{
    static public function imageStorageRoute(){


        //获取当前的url
        $realpath = str_replace('pics/','',Request::path());

        $path = storage_path() . $realpath;



        if(!file_exists($path)){
            //报404错误
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }
        //输出图片
        header('Content-type: image/jpg');
        echo file_get_contents($path);
        exit;
    }

}