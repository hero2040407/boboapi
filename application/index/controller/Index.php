<?php
namespace app\index\controller;
use think\Cookie;
use think\Db;
use BBExtend\BBRedis;

class Index extends \think\Controller
{
    public function index()
    {
        
        header("location:http://www.guaishoubobo.com/");
        exit;
        $res = Db::table('bb_shop_goods')->where('is_remove',0)->order('id desc')->limit(0,12)->select();
        foreach ($res as &$vo){
            if($vo['pic_list'] !='[]'){
                $vo['pic']=json_decode($vo['pic_list'],true)[0]['picpath'];
            }
        }
        echo $this->fetch('',['res'=>$res]);
        exit;
    }

    //格式化日期
    public function dataformat()
    {
        $res = Db::table('bb_users')->select();
        foreach ($res as $v){
            Db::table('bb_users')->where(['uid'=>$v['uid']])->update(['birthday'=>date('Y-m-d', strtotime($v['birthday']))]);
        }
        return ['code' => 1, 'message' => '操作成功!'];
    }

    public function redisceshi()
    {

        $data['b']=BBRedis::getInstance('push')->hGet('ceshi','b');
        $data['b']++;
        BBRedis::getInstance('push')->hset('ceshi','b','445');
        return ['data'=>BBRedis::getInstance('push')->hGetAll('ceshi'),'code' => 1, 'message' => '操作成功!'];
    }

    public function download()
    {
        $id = input('?param.id')?(string)input('param.id'):'0';
        $ip = $_SERVER['REMOTE_ADDR'];
        cookie(['prefix' => 'thinkbobo_']);

        if(strpos($_SERVER["HTTP_HOST"],'test') === false){
            $host = 'admin.yimwing.com';
        }elseif(strpos($_SERVER["HTTP_HOST"],'test.yim.com') === false){
            $host = 'test.yimwing.com:8080';
        }else{
            $host = 'admin.yim.com';
        }
        file_get_contents("http://$host/admin/api/connect_count/id/$id");

        if(!Cookie::has('guaishoubobodown')) {
            $cookie = time().rand(10,99);
            file_get_contents("http://$host/admin/api/down_count/id/$id/ip/$ip/cookie/$cookie");
            cookie('guaishoubobodown', $cookie);
        }
        echo $this->fetch();
    }

}
