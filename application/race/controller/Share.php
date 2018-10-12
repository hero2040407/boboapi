<?php
namespace app\race\controller;

use think\Controller;
use think\Db;
use BBExtend\Sys;

class Share extends Controller
{
    public $list;


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


    public function rank($race_id,$startid=0, $length=10,$search='',$self_uid=0)
    {
        $race = \BBExtend\model\Race::find( $race_id );
        if (!$race) {
            return ['code'=>0, 'message' =>'大赛信息错误' ];
        }

        $db = Sys::get_container_dbreadonly();
        if ($search) {
            $sql="select id, uid,pic,name,ticket_count from ds_register_log 
                   where zong_ds_id=? 
                     and has_pay=1 
                      and name like ? 
                   order by ticket_count desc 
                   limit ?,?";

            $user_arr = $db->fetchAll($sql,[ $race_id, $search.'%', $startid, $length ]);
        }else {
            $sql="select id, uid,pic,name,ticket_count from ds_register_log
                   where zong_ds_id=?
                     and has_pay=1
                   order by ticket_count desc,id asc
                   limit ?,?";

            $user_arr = $db->fetchAll($sql,[ $race_id,$startid, $length ]);
        }


        foreach ( $user_arr as $k=>$v ) {
            if ($self_uid) {
                $user_arr[$k]['my_ticket_count_today'] = $this->get_my_ticket_count_today($race_id,
                        $self_uid, $v['uid']);


            }else {
                $user_arr[$k]['my_ticket_count_today'] = 0;

            }
            $user_arr[$k]['rank'] = $this->get_rank_by_one($race_id, $v['uid']);

        }
        $myinfo=null;
        if ($self_uid) {
            $rank = $this->get_rank_by_one($race_id, $self_uid);
            if ($rank) {
                $sql="select * from ds_register_log where uid=? and zong_ds_id=?";
                $row = $db->fetchRow($sql,[ $self_uid, $race_id ]);
                $myinfo =[
                        'uid' =>$self_uid,
                        'pic' =>$row['pic'],
                         'name' =>$row['name'],
                        'ticket_count' =>$row['ticket_count'],
                        'rank' =>$rank,
                        'my_ticket_count_today' => $this->get_my_ticket_count_today($race_id,
                                $self_uid, $self_uid),


                ];

            }

        }
        $reward='';
        if ( $race->reward ) {
            $reward =  $race->reward;
        }

        $sql="select count(*) from  ds_register_log 
               where zong_ds_id=? 
                 and has_pay=1 ";
        $all_join_count = $db->fetchOne($sql,[ $race_id ]);

        return ['code'=>1,'data' => [ 'list' =>$user_arr,'myinfo' =>$myinfo ,
                'all_join_count' =>$all_join_count,
                'reward' =>$reward] ];

    }



    private function get_my_ticket_count_today($race_id,$self_uid, $target_uid)
    {

        $db = Sys::get_container_dbreadonly();
        $sql="
select count(*)   from ds_like
                   where race_id=?
                     and type=1
                     and self_uid=?
                     and target_uid=?
                
";
        //$this->list = $join_id_arr = $db->fetchCol($sql,[ $race_id ]);
        return $db->fetchOne($sql,[ $race_id, $self_uid, $target_uid ]);
    }



    private function get_rank_by_one($race_id,$uid)
    {
        $rank=0;
        if ($this->list) {

        }else {

          $db = Sys::get_container_dbreadonly();
          $sql="
select uid   from ds_register_log
                   where zong_ds_id=?
                     and has_pay=1
                   order by ticket_count desc,id asc
                
";
          $this->list = $join_id_arr = $db->fetchCol($sql,[ $race_id ]);

        }

        foreach ( $this->list as $all_uid ) {
            $rank++;
            if ( $all_uid==$uid ) {
                return $rank;
            }
        }
        return 0;

    }



}
