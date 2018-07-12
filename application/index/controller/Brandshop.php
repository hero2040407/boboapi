<?php
namespace app\index\controller;

use think\Db;
use app\user\controller\User;


class Brandshop extends \think\Controller
{
    public function index()
    {

    }

  
   public function info()
    {

        $id = input('?param.id')?input('param.id'):0;

        $res = Db::table('bb_brandshop')->where('id',$id)->find();
        
        if($res){
            echo $this->fetch('',['res'=>$res]);
            exit;
        }else{
            abort(404,'页面不存在!!请检查路径后再试~');
        }

    }

    //申请
    public function apply()
    {
        $user['uid'] = input('?param.uid')?input('param.uid'):0;
        $user['pic'] =  User::get_userpic($user['uid']);
        echo $this->fetch('',['user'=>$user,]);
        exit;
    }

    public function apply_summit()
    {
        $data['uid'] = input('?post.uid')?input('post.uid'):0;
        $data['jigou_name'] = input('?post.name')?input('post.name'):'';
        $data['lianxiren'] = input('?post.linkman')?input('post.linkman'):'';
        $data['phone'] = input('?post.phone')?input('post.phone'):'';
        $data['address'] = input('?post.address')?input('post.address'):'';
        $data['jianjie'] = input('?post.info')?input('post.info'):'';
        $data['create_time'] = time();
        if($data['uid'] != 0){
            $res = Db::table('bb_brandshop_application')->where(['uid'=>$data['uid']])->where('status','neq',2)->find();
            if($res){
                if($res['status'] == 0){
                    return ['code'=>0,'message'=>'您已经申请了品牌馆，请等待审核！'];
                }else if($res['status'] == 1){
                    return ['code'=>0,'message'=>'您已经拥有品牌馆了，无法重新申请！'];
                }
            }else{
                Db::table('bb_brandshop_application')->insert($data);
                return ['code'=>1];
            }
        }else{
            return ['code'=>0,'message'=>'用户重要信息不全!'];
        }

    }

}
