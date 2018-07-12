<?php
namespace app\race\controller;

use think\Controller;
use think\Db;


class Share extends Controller
{
    public function index(){
        $qd_id = input('?param.id')?input('param.id'):0;
        if($qd_id == 0){
            abort(404,'页面不存在!!请检查路径后再试~');
        }else{
            $res = Db::table('ds_race')->where(['id'=>$qd_id])->find();
            $race_res = Db::table('ds_race')->where(['id'=>$res['parent']])->find();
            if($race_res['start_time'] > time()){
                $state = 1; //大赛未开始
            }else{
                if($race_res['end_time'] > time()){
                    $state = 2; //大赛进行中
                }else{
                    $state = 3;//已结束
                }
            }
            
            $user = Db::table('bb_users')->where(['uid'=>$res['uid']])->find();
            echo $this->fetch('',['race_res'=>$race_res,'qd_id'=>$qd_id,'userpic'=>$user['pic'],'state'=>$state]);
        }
    }
}