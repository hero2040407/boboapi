<?php
namespace app\index\controller;

use think\Db;
use app\user\controller\User;


class Starmaker extends \think\Controller
{
    public function index()
    {
        $res = Db::table('bb_shop_goods')->where('is_remove',0)->order('id desc')->limit(0,12)->select();
        foreach ($res as &$vo){
            if($vo['pic_list'] !='[]'){
                $vo['pic']=json_decode($vo['pic_list'],true)[0]['picpath'];
            }
        }
        echo $this->fetch('',['res'=>$res]);
        exit;
    }

  
   public function info()
    {

        $uid = input('?param.uid')?input('param.uid'):0;

        $res = Db::table('bb_users_starmaker')->where('uid',$uid)->find();

        $people_num = Db::table('bb_record_invite_starmaker')->where(['status'=>3,'starmaker_uid'=>$uid])->count();

        if($res){
            if(!empty($res['detail_img'])){
                $res['detail_img']=explode(',',$res['detail_img']);
            }
            $res['detail_yinxiang']=explode(',',$res['detail_yinxiang']);
            $res['detail_shangxian']=explode(',',$res['detail_shangxian']);
            $user['pic'] =  User::get_userpic($uid);
            $user['nickname'] = User::get_nickname($uid);
            echo $this->fetch('',['res'=>$res,'user'=>$user,'people_num'=>$people_num]);
            exit;
        }else{
            abort(404,'页面不存在!!请检查路径后再试~');
        }

    }

}
