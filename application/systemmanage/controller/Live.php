<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\aliyun\Common;
class Live 
{
     const limit_count=50;// 限制只查多少人姓名。
     
     const task_name ='jobs22';
     const task_class_name ='\app\command\controller\Job22';
      
     
     /**
      * 童星排行榜，每10分钟一次。
      * 
      */
     public function rank_tongxing()
     {
         $redis = Sys::get_container_redis();
         $db = Sys::get_container_dbreadonly();
         $sql = "select uid from bb_users 
         where role in (1,3) and permissions <10
         and not_login=0
          order by uid asc
";
         
         // 注意，这里数量有几万，所以最好的办法是轮询。
         $query = $db->query($sql);
         
         $key = "tongxing_index20180704:list";
         $key2 = "tongxing_index20180704:list:backup";
         
         // 首先，清除key2
         $redis->delete($key2);
         $i=0;
         // 积分 = 粉丝数 + 点赞数/50
         while ( $row = $query->fetch() ) {
             $uid = $row['uid'];
             echo $i++;
             echo " ".$uid."\n";
             $fans_count = \BBExtend\user\Focus::getinstance($uid)
                ->get_fensi_count();
             $sql = 'select sum(`like`) from bb_record
                where uid =?';
             $zan = $db->fetchOne($sql,[ $uid ]);
             $zan = intval($zan);
             $zan = $zan/50;
             $zan = intval($zan);
             
             $score = $fans_count + $zan;
             
             $redis->zAdd($key2, $score, $uid);
         }
         
         $redis->delete($key);
         $redis->rename( $key2, $key );
         
     }
     
     
     /**
      * 童星排行榜2，每10分钟一次。
      *
      */
     public function rank_tongxing_renqi()
     {
         $redis = Sys::get_container_redis();
         $db = Sys::get_container_dbreadonly();
         $sql = "select uid from bb_users
         where role in (1,3) and permissions <10
         and not_login=0
          order by uid asc
";
         
         // 注意，这里数量有几万，所以最好的办法是轮询。
         $query = $db->query($sql);
         
         $key = "tongxing_index20180704:list:renqi";
         $key2 = "tongxing_index20180704:list:renqi:backup";
         
         // 首先，清除key2
         $redis->delete($key2);
         $i=0;
         // 积分 = 粉丝数 + 点赞数/50
         while ( $row = $query->fetch() ) {
             $uid = $row['uid'];
             $user = \BBExtend\model\User::find( $uid );
             
             echo $i++;
             
            
             $score = $user->get_count_for_renqi();
             echo " ".$uid." : {$score}"."\n";
             $redis->zAdd($key2, $score, $uid);
         }
         
         $redis->delete($key);
         $redis->rename( $key2, $key );
         
     }
     
     
     /**
      * 定时修改大赛名次
      */
     public function updatedsrank()
     {
         // 首先，查出所有大赛。
         $db = Sys::get_container_db_eloquent();
         $sql="
                select id from ds_race 
                where is_active=1 and parent=0 
                order by has_end asc, sort desc , start_time desc 
                 ";
         $ds_id_arr = DbSelect::fetchCol($db, $sql);
//          dump($ds_id_arr);
         foreach ($ds_id_arr as $ds_id) {
             $sql="
                 select ds_record.id,(
             select `like` from bb_record where bb_record.id = ds_record.record_id
           ) c_time  from ds_record
           where
             ds_id =?
           and exists (select 1 from bb_record
           where bb_record.id = ds_record.record_id
           and bb_record.audit=1
           and bb_record.is_remove=0
           )
           order by c_time desc 
           limit 500
                     ";
             $record_id_arr = DbSelect::fetchCol($db, $sql,[$ds_id]);// 注意并非record表主键。
             $rank=1;
             foreach ($record_id_arr as $id) {
                 
                 $ds_record = \BBExtend\model\DsRecord::find($id);
                 $ds_record->rank = $rank;
                 $ds_record->save();
                // echo "ds_id:".$ds_id." id:{$id} rank:{$rank}\n";
                 $rank++;
             }
             
//              break;
             
         }
         
     }
     
     /**
      * 
      * 该定时任务，修改已经断流但数据库错误的直播流，把状态设为断流。
      * 
      * 谢烨，你先查出不符合条件的。然后查播放时间。
      * 如果大于两分钟的，则修改之。
      * 
      * 
      */
    public function updatepush()
    {
        Sys::display_all_error();
       // return;
        
        $domain_arr=["www.yimwing.com"];
        foreach (range(1,19) as $id  ) {
            $domain_arr[]= "push{$id}.yimwing.com";
        }
        foreach ($domain_arr as $domain) {
        
            $result = Common::describeLiveStreamsOnlineList($domain);
           
            $db = Sys::get_container_db();
            
            $limit_time = time() - 1* 60; // 只查小于此时间的。
            $sql ="update bb_push set event='publish_done'  where create_time < {$limit_time}
    and event= 'publish'  and domain='{$domain}' ";
            if ($result) {
                $sql .= " and stream_name not in (?) 
                        and not exists (
                          select 1 from bb_users
                           where bb_users.uid = bb_push.uid
                             and bb_users.permissions=99
                        )
                        and price_type=1
                        ";
                $sql = $db->quoteInto($sql, $result);
            }
            $db->query($sql);
            echo "ok";
            
        }
    }
    
    /**
     * 首页更新
     */
    public function update_index()
    {
        $db = Sys::get_container_db();
       
        
        $redis = Sys::getredis11();
        $key = "index:recommend:list";
       
        
            $sql="
                  select bb_record.*,bb_subject_movie.subject_id from bb_record
                    left join bb_subject_movie
                    on bb_subject_movie.room_id = bb_record.room_id
                    where bb_subject_movie.id >0
                     and  bb_record.audit=1
                     and bb_record.is_remove=0
                     and bb_subject_movie.is_recommend = 1
                     and exists(
                       select 1 from bb_subject
                         where bb_subject.is_show=1
                           and bb_subject.id = bb_subject_movie.subject_id
                       )
                   order by bb_subject_movie.sort desc
                    limit 800
                    ";
            $records = $db->fetchAll($sql); // 这是所有的推荐的短视频的集合
            $redis->set($key, serialize($records) );
        
    }
    
    
    public function test()
    {
        echo Common::accessKeyId;
    }
    
    /**
     *
     * 该定时任务，把邀约过期的活动的has_end 设为1
     *
     *
     */
    public function act_has_end()
    {
//         Sys::display_all_error();
//         $result = Common::describeLiveStreamsOnlineList();
         
        $db = Sys::get_container_db();
        $time = time();
        $sql = "update bb_task_activity
                 set has_end =1
                 where end_time< '{$time}' 
                
                ";
        $db->query($sql);
        $sql = "update bb_task_activity
        set has_end =0
        where end_time> '{$time}'
        
        ";
        $db->query($sql);
        
        $sql = "update ds_race
        set has_end =1
        where end_time< {$time}
        ";
        $db->query($sql);
        
        $sql = "update ds_race
        set has_end =0
        where end_time> {$time}
        ";
        $db->query($sql);
        
        
        echo "ok";
    }
    
    /**
     *
     * 该定时任务，给hot_days+1,然后下架所有的超期视频。
     *
     * 谢烨，同时，给过期的消息的置顶取消！！。
     *
     */
    public function hot_record()
    {
        //         Sys::display_all_error();
        //         $result = Common::describeLiveStreamsOnlineList();
        $db = Sys::get_container_db();
        $time = time();
        //先取消过期的消息
        $sql ="update bb_msg set sort=0 where overdue_time < '{$time}'";
        $db->query($sql);
        
        
        
        $sql = "update bb_record
        set hot_days = hot_days+1 
        where heat>0
        ";
        $db->query($sql);
        
        // 
        $sql = "select * from bb_label";
        $result = $db->fetchAll($sql);
        foreach ($result as $v) {
            if ($v['name']=='其它') {
                $label=0;
            }else {
                $label = $v['id'];
            }
            $sql = "update bb_record set heat = 0 
             where label='{$label}' and hot_days > {$v['hot_days']}";
       //     Sys::debugxieye($sql);
            $db->query($sql);
        }
        $filename='http://admin.yimwing.com/admin/api/updatapassword';
        $result = file_get_contents($filename);
        $db->insert("bb_alitemp", [
            'create_time'=>date("Y-m-d H:i:s"),
            'url' => $filename,
            'content' => var_export($result, 1),
            
        ]);
        
        echo "ok";
    }
    
    /**
     * 这是假关注任务，每分钟都要检查。
     * 
     * 特殊用户每添加一个正常粉丝，自动加3-5个机器人粉丝
     * 只要是新注册用户自动添加 关注特殊用户群体，取10个随机的特殊用户。
     * 随机互相点赞，用户在线10分钟以上，自动点赞任意视频。
     * 
     * 分享5次送10个波币，然后今天就不送了。
     */
    public function sham_focus()
    {
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $time  = date("Y-m-d H:i:s");
        $time1  = preg_replace('#\d\d$#', '00', $time);
        $time1 = strtotime($time1);
        $time2 = $time1 + 59;
        
        $sql ="select * from bb_system_task where type =1 and task_at between {$time1} and {$time2} ";
        $query = $db->query($sql);
        while ( $row = $query->fetch() ) {
            $help = \BBExtend\user\Focus::getinstance($row['uid'] );
            $help->focus_guy($row['target_uid'], $row['task_at'] );
            $sql = "update bb_system_task set has_finish=1 where id = {$row['id']}";
            $db2->query($sql);
            
        }
        
    }
    
    
    
    /**
     * 系统定时任务，推送消息半小时合并。
     */
    public function push_message(){
        Sys::display_all_error();
        $this->push_message119(); // 查出所有的119 被赞
        $this->push_message121(); // 查出所有的121 打赏
        $this->push_message122(); // 查出所有的122 被关注
        $this->push_message123(); // 查出所有的123  短视频上传。
    }
    
    
    public function push_message123()
    {
        Sys::display_all_error();
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $type=123;
        $limit_count=self::limit_count;
        $sql="select uid from bb_msg_cache
    where type={$type}
    group by uid
    limit 1000
                ";
        $ids = $db->fetchCol($sql);
        //uid是消息接收者
        foreach ($ids as $uid) {  // 对于每个人，下面的代码
            $target_uid = intval($uid);
            //先查数量
            $sql ="select count(*) from bb_msg_cache where type={$type} and uid = {$target_uid}";
            $count = $db->fetchOne($sql);
    
            //再查出具体人
            $sql ="select * from bb_msg_cache
            where type={$type} and uid = {$target_uid}
            limit {$limit_count}
            ";
            $new = $db->fetchAll($sql);
            $this->_push123($new, $target_uid, $count);
    
            $sql ="delete from bb_msg_cache where type={$type} and uid = {$target_uid}";
            $db->query($sql);
    
        }
    }
    
    /**
     * 给某一个用户送礼消息推送。
     * @param unknown $new
     * @param unknown $target_uid
     * @param unknown $record_id
     * @param number $all_count
     */
    private function _push123($new,$target_uid, $all_count=0)
    {
        \Resque::setBackend('127.0.0.1:6380');
        //    #玩家昵称1#，#玩家昵称2#等XX位用户上传了新的短视频
        $db = Sys::get_container_db();
    
        $s='';
        $new2=[];
        foreach ($new as  $v) {
            $user  = \app\user\model\UserModel::getinstance($v['other_uid']);
//             $s .= $user->get_nickname() . "，";
            $new2[]=$user->get_nickname();
        }
        $new2 = array_unique($new2);
//         if (preg_match('/，$/', $s)) {
//             $s = preg_replace('/^(.+?)，$/', '$1', $s);
//         }
        $s .=  implode('，', $new2). "等{$all_count}位用户上传了新的短视频";
   // echo $target_uid.":". $s."\n";
        $args = array(
            'target_uid' => $target_uid,
            'info'  => $s,
            'time' => time(),
            'type' => '123',
        );
        \Resque::enqueue(self::task_name, self::task_class_name, $args);
    
    
    }
    
    public function push_message121()
    {
        Sys::display_all_error();
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $type=121;
        $limit_count=self::limit_count;
        $sql="select uid from bb_msg_cache
    where type={$type}
    group by uid
    limit 1000
                ";
        $ids = $db->fetchCol($sql);
        //uid是消息接收者
        foreach ($ids as $uid) {  // 对于每个人，下面的代码
            $target_uid = intval($uid);
            //先查数量
            $sql ="select count(*) from bb_msg_cache where type={$type} and uid = {$target_uid}";
            $count = $db->fetchOne($sql);
            
            //再查出具体的送礼的人
            $sql ="select * from bb_msg_cache 
            where type={$type} and uid = {$target_uid}
            limit {$limit_count}
            ";
            $new = $db->fetchAll($sql);
            $this->_push121($new, $target_uid, $count);
            
            $sql ="delete from bb_msg_cache where type={$type} and uid = {$target_uid}";
            $db->query($sql);
    
        }
    }
    
    /**
     * 给某一个用户送礼消息推送。
     * @param unknown $new
     * @param unknown $target_uid
     * @param unknown $record_id
     * @param number $all_count
     */
    private function _push121($new,$target_uid, $all_count=0)
    {
        \Resque::setBackend('127.0.0.1:6380');
        //    #玩家昵称1#，#玩家昵称2#等XX位用户给你送了礼物
        $db = Sys::get_container_db();
        
        $s='';
        $new2=[];
        foreach ($new as  $v) {
            $user  = \app\user\model\UserModel::getinstance($v['other_uid']);
//             $s .= $user->get_nickname() . "，";
            $new2[] = $user->get_nickname();
            
        }
        $new2 = array_unique($new2);
//         if (preg_match('/，$/', $s)) {
//             $s = preg_replace('/^(.+?)，$/', '$1', $s);
//         }
        $s .= implode('，', $new2).  "等{$all_count}位用户给你送了礼物";
    
        $args = array(
            'target_uid' => $target_uid,
            'info'  => $s,
            'time' => time(),
            'type' => '121',
        );
        \Resque::enqueue(self::task_name, self::task_class_name, $args);
    
    
    }
    
    
    public function push_message119()
    {
        Sys::display_all_error();
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $type=119;
        $limit_count=self::limit_count;
        $sql="select uid from bb_msg_cache
    where type={$type}
    group by uid
    limit 1000            
                ";
        $ids = $db->fetchCol($sql);
        //uid是消息接收者
        foreach ($ids as $uid) {  // 对于每个人，下面的代码
            $target_uid = intval($uid);
            $sql = "select other_record_id,count(*) as c from bb_msg_cache  
            where type={$type} and uid = {$target_uid}
            group by other_record_id
            limit 100
            ";
            $record_ids  = $db->fetchAll($sql);
            foreach ( $record_ids as $record_id ) {
                // 谢烨，现在是每个人，每个 短视频id
                $sql = "select id, other_uid from bb_msg_cache  
            where type={$type} and uid = {$target_uid}
            and other_record_id = {$record_id['other_record_id']}
            limit {$limit_count}
            ";
                $result = $db->fetchAll($sql);
//                 $query = $db->query($sql);
                $new =[];
//                 while($row = $query->fetch()) {
                foreach ($result  as $row) {
                   // $other_uid = $row['other_uid'];
                    $new[ $row['id'] ] = $row['other_uid'];
                }
                $this->_push119($new,$target_uid, $record_id['other_record_id'],$record_id["c"] );
                $sql ="delete from bb_msg_cache where type={$type} and uid = {$target_uid} 
                   and  other_record_id = {$record_id['other_record_id']}
                ";
                $db->query($sql);
            }
            
        }
    }
    
    
    

    /**
     * 给某一个用户发点赞推送。
     * @param unknown $new
     * @param unknown $target_uid
     * @param unknown $record_id
     * @param number $all_count
     */
    private function _push119($new,$target_uid, $record_id,$all_count=0)
    {
        \Resque::setBackend('127.0.0.1:6380');
    //    #玩家昵称1#，#玩家昵称2#等XX位用户赞了你的视频#视频标题#
        $db = Sys::get_container_db();
        $sql = "select title from bb_record where id=".intval($record_id);
        $title = $db->fetchOne($sql);
        $title=strval($title);
        $s='';
        $new2 =[];
        foreach ($new as $k => $v) {
            $user  = \app\user\model\UserModel::getinstance($v);
//             $s .= $user->get_nickname() . "，";
            $new2[]= $user->get_nickname();
            
        }
        $new2 = array_unique($new2);
//         if (preg_match('/，$/', $s)) {
//             $s = preg_replace('/^(.+?)，$/', '$1', $s);
//         }
        $s .=  implode('，', $new2) . "等{$all_count}位用户赞了你的视频{$title}";
        
        
        $args = array(
            'target_uid' => $target_uid,
             'info'  => $s,
             'time' => time(),
            'type' => '119',
        );
        \Resque::enqueue(self::task_name, self::task_class_name, $args);
        
    }
    
    // 关注
    public function push_message122()
    {
        Sys::display_all_error();
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $type=122;
        $limit_count=self::limit_count;
        $sql="select uid from bb_msg_cache
        where type={$type}
        group by uid
        limit 1000
        ";
        $ids = $db->fetchCol($sql);
        //uid是消息接收者
        foreach ($ids as $uid) {  // 对于每个人，下面的代码
            $target_uid = intval($uid);
            //先查数量
            $sql ="select count(*) from bb_msg_cache where type={$type} and uid = {$target_uid}";
            $count = $db->fetchOne($sql);
    
            //再查出具体人
            $sql ="select * from bb_msg_cache
            where type={$type} and uid = {$target_uid}
            limit {$limit_count}
            ";
            $new = $db->fetchAll($sql);
            $this->_push122($new, $target_uid, $count);
    
            $sql ="delete from bb_msg_cache where type={$type} and uid = {$target_uid}";
            $db->query($sql);
    
        }
    }
    
    
    
    
    /**
     * 给粉丝发关注推送。
     * @param unknown $new
     * @param unknown $target_uid
     * @param unknown $record_id
     * @param number $all_count
     */
    private function _push122($new,$target_uid, $all_count=0)
    {
        \Resque::setBackend('127.0.0.1:6380');
        //    #玩家昵称1#，#玩家昵称2#等XX位用户关注了你。
        $db = Sys::get_container_db();
       
        $s='';
        $new2 =[];
        foreach ($new as $k => $v) {
            $user  = \app\user\model\UserModel::getinstance($v['other_uid']);
            $new2[]= $user->get_nickname();
        }
        $new2 = array_unique($new2);
        $s .=  implode('，', $new2) . "等{$all_count}位用户成为了您的新粉丝";
  //  echo $s;
        $args = array(
            'target_uid' => $target_uid,
            'info'  => $s,
            'time' => time(),
            'type' => '122',
        );
        \Resque::enqueue(self::task_name, self::task_class_name, $args);
    
    }
    
    
    
    
   
}