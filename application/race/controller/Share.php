<?php
namespace app\race\controller;

use think\Controller;
use think\Db;
use BBExtend\Sys;

class Share extends Controller
{
    public function index(){
        $qd_id = input('?param.id')?input('param.id'):0;
        if($qd_id == 0){
            abort(404,'页面不存在!!请检查路径后再试~');
        }else{
            
            $this->redirect('https://bobo.yimwing.com/webapp/#/contest/dasai/detail/'.$qd_id,302);
            
            
//             $res = Db::table('ds_race')->where(['id'=>$qd_id])->find();
//             $race_res = Db::table('ds_race')->where(['id'=>$res['parent']])->find();
//             if($race_res['start_time'] > time()){
//                 $state = 1; //大赛未开始
//             }else{
//                 if($race_res['end_time'] > time()){
//                     $state = 2; //大赛进行中
//                 }else{
//                     $state = 3;//已结束
//                 }
//             }
            
//             $user = Db::table('bb_users')->where(['uid'=>$res['uid']])->find();
//             echo $this->fetch('',['race_res'=>$race_res,'qd_id'=>$qd_id,'userpic'=>$user['pic'],'state'=>$state]);
        }
    }
    
    
    public function rank($race_id,$startid=0, $length=10,$search='')
    {
        $db = Sys::get_container_dbreadonly();
        if ($search) {
            $sql="select uid,pic from ds_register_log 
                   where zong_ds_id=? 
                     and has_pay=1 
                      and uid =? 
                   order by ticket_count desc 
                   limit ?,?";
            
            $user_arr = $db->fetchAll($sql,[ $race_id, $search, $startid, $length ]);
        }else {
            $sql="select uid,pic from ds_register_log
                   where zong_ds_id=?
                     and has_pay=1
                   order by ticket_count desc
                   limit ?,?";
            
            $user_arr = $db->fetchAll($sql,[ $race_id,$startid, $length ]);
        }
        
        
        foreach ( $user_arr as $user ) {
            
            
        }
        return ['code'=>1,'data' => [ 'list' =>$user_arr ] ];
        
    }
    
    
}