<?php
namespace app\apptest\controller;


use BBExtend\Sys;
use BBExtend\DbSelect;


class TempV2
{
    
    // 下单
    public function index1()
    {
        
        echo 23;
        
    }
    
    public function remove_register_7049564()
    {
        $uid = 7049564;
        $db = Sys::get_container_db();
        $sql="delete from ds_register_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_prepare where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_record where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_user_log where uid = {$uid}";
        $db->query($sql);
        echo "清除{$uid}大赛报名记录ok。<br>";
        
    }
    
    public function remove_register_qsq_10010()
    {
        $uid = 10010;
        $db = Sys::get_container_db();
        $sql="delete from ds_register_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_prepare where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_record where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_user_log where uid = {$uid}";
        $db->query($sql);
        echo "清除{$uid}大赛报名记录ok。<br>";
        
    }
    
    
    
    public function remove_register_qsq()
    {
        $uid = 6449346;
        $db = Sys::get_container_db();
        $sql="delete from ds_register_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_prepare where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_record where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_user_log where uid = {$uid}";
        $db->query($sql);
        echo "清除{$uid}大赛报名记录ok。<br>";
        
        $uid = 8065658;
        $db = Sys::get_container_db();
        $sql="delete from ds_register_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_prepare where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_record where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_user_log where uid = {$uid}";
        $db->query($sql);
        
        
        echo "清除{$uid}大赛报名记录ok。";
    }
    
    
    public function remove_register_mzj()
    {
        $uid = 10003;
        $db = Sys::get_container_db();
        $sql="delete from ds_register_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_prepare where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_record where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_user_log where uid = {$uid}";
        $db->query($sql);
        
        
        echo "清除{$uid}大赛报名记录ok。";
    }
    
    public function remove_register()
    {
        $uid = 8064553;
        $db = Sys::get_container_db();
        $sql="delete from ds_register_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_prepare where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_record where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_user_log where uid = {$uid}";
        $db->query($sql);
       
        $uid = 7049564;
        $db = Sys::get_container_db();
        $sql="delete from ds_register_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_log where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_money_prepare where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_record where uid = {$uid}";
        $db->query($sql);
        $sql="delete from ds_user_log where uid = {$uid}";
        
        
        echo "清除{$uid}大赛报名记录ok。";
    }
    
    public function test()
    {
        echo <<<html
<h1>这是一个弹框测试页面</h1>
<img src ='http://bobo-upload.oss-cn-beijing.aliyuncs.com/public/dasai/2018-04-17/img/nullData.8b363e9.png'>
html;
        
    }
    
    // 抢单
    public function index2($uid,$record_id){
//         $uid =  3631247;
//         $record_id = $data->get_record_id();
        $time = time();
        
        $invite =  \BBExtend\model\RecordInviteStarmaker::where(
                'record_id', $record_id)->first();
        if (!$invite) {
            return  ['code'=>0, 'message' => 'invite 不存在' ];
        }
        
        if ( $invite->starmaker_uid ) {
            // 这是指定的情况，此时，如单已完成，返回错误，如单未完成，返回正确。
            if ( $invite->starmaker_uid == $uid ) {
                
                if ($invite->status==1) {
                    return  ['code'=>1, 'message' => '' ];
                }else {
                    return  ['code'=>0, 'message' => '此单已点评过，不可重复点评' ];
                }
            }else {
                
                return  ['code'=>0, 'message' => '此单已被他人抢单成功。' ];
            }
            
        }else {
            // 这是抢单的情况，
            // 首先，本人是星推官吗,外面已经检查过，
            // 然后，查当前用户，是否有 其他的status=1 的单子。
            $db = \BBExtend\Sys::get_container_db();
            $db->closeConnection();
            $db = \BBExtend\Sys::get_container_db();
            $sql="select count(*)
                   from bb_record_invite_starmaker
                  where starmaker_uid = {$uid}
                    and status=1
                    and record_id != {$record_id}
";
            $count = $db->fetchOne($sql);
            if ($count) {
                return  ['code'=>0, 'message' => '您有其他邀请未点评，不能抢单' ];
            }else {
                // 抢单成功。
                $invite->starmaker_uid = $uid;
                $invite->save();
                return  ['code'=>1, 'message' => '抢单成功。' ];
            }
        }
        
    }
    
    public function insert_ds_register_log($field_id){
       
        if ( \BBExtend\Sys::is_product_server() ) {
            exit;
        }
        
        $db =  \BBExtend\Sys::get_container_db_eloquent();
        
        $field = \BBExtend\backmodel\RaceField::find( $field_id );
        if (!$field) {
            return ['code'=>0,'message'=> '赛区不存在'  ];
        }
        
        $sql="select uid from bb_users 
               where uid not in (select uid from ds_register_log) limit 10";
        $result = \BBExtend\DbSelect::fetchCol($db, $sql);
        foreach ($result as $uid) {
          $db::table("ds_register_log")->insert([
                'ds_id' => $field_id,
                'zong_ds_id' =>$field->race_id,
                'has_join' =>1,
                'has_pay' =>1,
                'has_dangan' => 1,
                'uid' =>$uid,
                  'name' => '真实姓名'.mt_rand(100000,999999),
                
           ]  );
           echo "insert into ds_register_log success!<br>大赛id:{$field->race_id},<br>
赛区id：{$field_id}。
";
        }
        
    }
   
    
    public function insert_ds_message_log($ds_id,$uid){
        
        if ( \BBExtend\Sys::is_product_server() ) {
            exit;
        }
        
        $db =  \BBExtend\Sys::get_container_db_eloquent();
        
        $ds_id = intval($ds_id);
        $uid = intval($uid);
        
        $arr = [
                ['content'=>"恭喜你成功晋级恭喜你成功晋级恭喜你成功晋级恭喜你成功晋级恭喜你成功晋级恭喜你成功晋级",
                        'title'=>"成功晋级",
                        'create_time'=>time() ,
                        'ds_id' => $ds_id,
                        'uid' => $uid,
                        
                ],
                ['content'=>"报名成功，请上传视频。",
                        'title'=>"报名成功",
                        'create_time'=>time() ,
                        'ds_id' => $ds_id,
                        'uid' => $uid,
                ],
                
                
        ];
        
        
        foreach ($arr as $v) {
            $db::table("ds_user_log")->insert($v );
            echo "insert into ds_user_log success!<br>";
        }
        
    }
    
    
    public function news_comment_set()
    {
        $db2 = Sys::get_container_db_eloquent();
        $db = Sys::get_container_db();
        $db3 = Sys::get_container_dbreadonly();
        $sql="select id from web_article order by id asc ";
        $query = $db->query($sql);
        
        while ( $row= $query->fetch()) {
            echo $row['id'];
            echo "\n";
            
            $sql="select count(*)  from web_article_comment where status=1 and is_reply=0 and article_id=?";
            $count = $db3->fetchOne($sql, $row['id']);
            $db2::table('web_article')->where( 'id',$row['id'] )->update(['comment_count'=> $count ]);
            
            echo $row['id'];
            echo "\n";
        }
    }
    
    
    public function add_muti_audition()
    {
        $db = Sys::get_container_db();
        $db2 = Sys::get_container_db_eloquent();
        
        $type='HL01';
        
        $this->add_muti_audition_by_type($type);
        
        
        $type='YS01';
        $this->add_muti_audition_by_type($type);
        $type='ZY01';
        $this->add_muti_audition_by_type($type);
        echo "all ok";
        
    }
    
    private function add_muti_audition_by_type($type)
    {
        $db = Sys::get_container_db();
        $db2 = Sys::get_container_db_eloquent();
        
        
        foreach (range(1,1000) as $v) {
            
            while (true) {
               $serial = $this->create_serial($type);
               if (!$this->has_exists($serial)) {
                  $db->insert("bb_audition_card", [
                          'serial' =>$serial,
                          'online_type' =>2,
                          'status' =>1,
                          'create_time' =>time(),
                          'type' =>$type,
                  ]);
                  echo  $v.":". $serial ."\n";
                  break;
               }
            
            }
        }
        
    }
    
    
    
    
    /**
     * NO: YS00ASHS       生成规则   前两位代表分类YS  表示影视分类 ， 第三第四位表示版本，2位数字表示 ，后四位是随机字符，0~9A-Z 表示
怪兽web主管 - 孙涵予  17:27:53

HL 葫芦兄弟， ZY 综艺类   YS 影视类

     * @param unknown $id
     * @return boolean
     */
    
    private function has_exists($id){
        $db = Sys::get_container_db_eloquent();
        $sql = "select count(*) from bb_audition_card where serial=?";
        $count =DbSelect::fetchOne($db, $sql,[ $id ]);
        if ($count) {
            return true;
        }
        return false;
        
    }
    
    
    
    private function create_one(){
        $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr( str_shuffle($str),0,1 );
    }
    
    private function create_serial($type)
    {
        
        $str  = '';
        
        foreach (range(1,4) as $v) {
            $str .= $this->create_one();
        }
        
        return $type . $str;
    }
    
    
    
}






