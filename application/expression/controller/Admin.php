<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/7/6
 * Time: 13:21
 */

namespace app\expression\controller;
use think\Controller;
use think\Db;

class Admin extends Controller
{

    public function index()
    {
       return view();
    }

    public function add_image()
    {
//        $title = ;
//        $url = ;
//        $currency_type = ;
//        $currency_num = ;
//        $heat_level = ;
        $packageDB['title'] = input('?post.title')?(string)input('post.title'):'';
        $packageDB['url'] = '';
        $id = Db::table('bb_expression_package')->insert();
        $id = Db::getLastInsID();
        $type=array("jpg","gif","jpeg","png");//文件上传类型
        //按时间文件夹存放头像
        $file = request()->file('image');
        $prefix = date('Y-m-d');
        $httppath = '/public/exp/'.$prefix.'/';
        $bigpicpath = '.'.$httppath;
        if (!is_dir($bigpicpath)){
            mkdir($bigpicpath,0775,true);
        }

        if ($file and in_array(pathinfo($file->getInfo()['name'],PATHINFO_EXTENSION), $type)){
            $info = $file->rule('uniqid')->move($bigpicpath);
            $bigpic = $httppath.$info->getFilename();
        }else{
            $bigpic = '/uploads/bigpic/default.jpg';
        }
    }
}