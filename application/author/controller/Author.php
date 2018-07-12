<?php
namespace app\author\controller;

use think\Controller;
use think\Db;


class Author extends Controller
{
    public function index()
    {
        $uid = input('?param.id') ?(int) input('param.id') : 0;

        return $this->fetch('./author/index.html');
    }

    public function hatch()
    {
        $uid = input('?param.id') ?(int) input('param.id') : 0;

        return $this->fetch('./author/index.html');
    }

    //已入住怪兽
    public function get_cer_pic()
    {
        return $this->fetch('./share/task_activity.html');
    }

    //最新投稿怪兽
    public function get_up_pic()
    {
        return $this->fetch('./share/task_activity.html');
    }

    public function up_pic(){
        $uid = input('?post.uid') ?(int) input('post.uid') : '';
        $type=array("log");//文件上传类型
        $file =  request()->file('log');
        $prefix = date('Y-m-d');
        $httppath = '/uploads/androidlog/'.$uid.'/'.$prefix;
        $bigpicpath = '.'.$httppath;
        if (!is_dir($bigpicpath)){
            mkdir($bigpicpath,0775,true);
        }
        if ($file and in_array(pathinfo($file->getInfo()['name'],PATHINFO_EXTENSION), $type)) {
            $file->move($bigpicpath,'');
        }
    }

}
?>