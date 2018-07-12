<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/17
 * Time: 15:14
 */

namespace BBExtend;


use app\user\controller\User;
use think\Db;
use BBExtend\Sys;
use BBExtend\message\Message;
use BBExtend\user\exp\Exp;

class BBComments extends Level
{
    /**
     * 获得评论列表
     * @param string $table 表名
     * @param string  $replay_table 回复表名
     * @param int  $activity_id 活动ID
     * @return array
     */
    public static function Get_comments($table,$activity_id,$start_id,$length,$like_table,$my_uid)
    {
        $activity_id= intval($activity_id);
        $start_id=intval($start_id);
        $length=intval($length);
        
//         $CommentDB_Array = Db::table($table)
//           ->where("uid >0")
//           ->where([  'activity_id'=>$activity_id,'audit'=>1,'is_remove'=>0])->order(['time'=>'desc'])->limit($start_id,$length)->select();
  
          $DataArray = array();
        $db = Sys::get_container_db();
        $sql="
           select {$table}.*,
 CASE WHEN bb_users.permissions=5  THEN 1
 ELSE 0 END p
from {$table}
  left join  bb_users
   on bb_users.uid = {$table}.uid
            where {$table}.uid >0
              and {$table}.activity_id={$activity_id}
              and {$table}.audit =1
              and {$table}.is_remove=0
            order by p desc, {$table}.time desc
            limit {$start_id},{$length}
                ";
        $CommentDB_Array = $db->fetchAll($sql);
        $CommentDB_Array= (array)$CommentDB_Array;
        foreach ($CommentDB_Array as $CommentDB)
        {
            $Data = array();
            $uid = (int)$CommentDB['uid'];
            $Data['id'] = (int)$CommentDB['id'];
            $Data['uid'] = $uid;
            
            $userhelp = \app\user\model\UserModel::getinstance($uid);
            
            
            $user_detail = \BBExtend\model\User::find( $uid );
            
            $Data['role'] = $user_detail->role;
            $Data['frame'] = $user_detail->get_frame();
            $Data['badge'] = $user_detail->get_badge();
            
            //谢烨20160922，加vip返回字段
            $Data['vip'] = $userhelp->get_user_vip() ;
            
            //用户名称
            $Data['nickname'] = $userhelp->get_nickname();
            //头像
            $Data['pic'] = $userhelp->get_userpic();
            //点赞数量
            $Data['like_count'] = self::get_likes($like_table,$Data['id'],1);
            $Data['is_like'] = (bool)self::is_like($like_table,$my_uid,$Data['id'],1);
            //回复数量
            $Data['reply_count'] = (int)$CommentDB['reply_count'];
            //最新回复时间
            $Data['reply_time'] = $CommentDB['reply_time'];
            $Data['permissions'] = $userhelp->get_permission();
            
            // xieye 2016 10
            if (in_array($table, ['bb_activity_comments','bb_record_comments',
                'bb_rewind_comments', 'bb_task_comments',])) {
                // xieye bug
                $sql ="select count(*) from {$table}_reply where comments_id= {$Data['id']}
                  and audit=1 and is_remove=0
                ";
                $Data['reply_count'] =$db->fetchOne($sql);
                if (!$Data['reply_count']) {
                   $Data['reply_time'] = null;
                }else {
                  
                    $sql ="select time  from {$table}_reply where comments_id= {$Data['id']}
                    and audit=1 and is_remove=0 order by time desc limit 1
                    ";
                    $Data['reply_time']  = $db->fetchOne($sql);
                }
            }
            
            
            
            $Data['time'] = $CommentDB['time'];
            //内容
            $Data['content'] = $CommentDB['content'];
            $Data['score'] = (int)$CommentDB['score'];
            $Data['age'] = $userhelp->get_userage();
            $Data['sex'] = $userhelp->get_usersex();
            
            array_push($DataArray,$Data);
        }
        return $DataArray;
    }
    /**
     * 获得评论数量
     * @param string $table 表名
     * @param string  $replay_table 回复表名
     * @param int  $activity_id 活动ID
     * @return int
     */
    public static function Get_comments_count($table,$activity_id)
    {
        //xieye count
        return Db::table($table)
         ->where("uid >0")
          ->where(['activity_id'=>$activity_id,'audit'=>1,'is_remove'=>0])->count();
    }
    /**
     * 获得回复评论列表
     * @param string $table 表名
     * @param string  $content 内容
     * @param int  $comments_id 评论ID
     * @return array
     */
    public static function Get_reply_comments($table,$comments_id,$start_id,$length,$like_table,$uid)
    {
        $reply_CommentDB_Array = Db::table($table)->where(['comments_id'=>$comments_id,'audit'=>1,'is_remove'=>0])->order(['time'=>'desc'])->limit($start_id,$length)->select();
        $DataArray = array();
        foreach ($reply_CommentDB_Array as $replyDB)
        {
            $Data = array();
            $reply_uid = (int)$replyDB['uid'];
            $Data['id'] = (int)$replyDB['id'];
            $Data['uid'] = $reply_uid;
            
            //谢烨20160927，加vip返回字段
            $Data['vip'] = 0 ;
            
            $user =  \BBExtend\model\User::find($reply_uid);
            $Data['role'] = $user->role;
            $Data['frame'] = $user->get_frame();
            $Data['badge'] = $user->get_badge();
            
            
            
            $Data['nickname'] = User::get_nickname($reply_uid);
            $Data['pic'] = User::get_userpic($reply_uid);
            //点赞数量
            $Data['like_count'] = self::get_likes($like_table,$Data['id'],2);
            $Data['is_like'] = (bool)self::is_like($like_table,$uid,$Data['id'],2);
            $Data['time'] = $replyDB['time'];
            //内容
            $Data['content'] = $replyDB['content'];
            $Data['age'] = BBUser::get_userage($reply_uid);
            $Data['sex'] = BBUser::get_usersex($reply_uid);
            array_push($DataArray,$Data);

        }
        return $DataArray;
    }
    /**
     * 发表评论
     * @param string $table 表名
     * @param string  $content 内容
     * @param int  $activity_id 活动id
     * @param int  $uid 用户ID
     * @param int  $Score 评价分数 默认为0
     * @return int
     */
    public static function Send_comments($table,$uid,$activity_id,$content,$Score = 0)
    {
        $Data = array();
        
        $minganci_help = new \BBExtend\model\Minganci();
        $content = $minganci_help->filter_by_asterisk($content);
        
        $Data['content'] = $content;
        $Data['activity_id'] = $activity_id;
        $Data['time'] = time();
        $Data['uid'] = $uid;
        $Data['reply_count'] = 0;
        $Data['audit'] = 0;
        $Data['is_remove'] = 0;
        $Data['score'] = $Score;
        //self::add_user_exp($uid,LEVEL_COMMENTS);
        Exp::getinstance($uid)->set_typeint(Exp::LEVEL_COMMENTS)->add_exp();
        // xieye 201708 成就系统
//         $ach = new \BBExtend\user\achievement\Pinglun($uid);
//         $ach->update(1);
        
        // xieye 2016 自动审核管理员发帖
        $temp = \BBExtend\BBUser::get_user($uid);
        if ($temp && $temp['permissions']== \BBExtend\fix\Permission::bb_user_permission_admin ) {
            $Data['audit'] = 1;
        }
        
        Db::table($table)->insert($Data);
        
        // xieye 2016 10
        \BBExtend\user\Tongji::getinstance($uid)->comment();
        
        if (
                 in_array($table, ['bb_record_comments','bb_rewind_comments',])
                &&  $Data['audit'] == 1
                
                ){
            $db = Sys::get_container_db();
            $sql="select nickname,pic from bb_users where uid=".intval($uid);
            $row = $db->fetchRow($sql);
            $nickname = $row['nickname'];
            $pic = $row["pic"];
            
            $sql = "select title from bb_record where id = ".intval($activity_id);
            $title = $db->fetchOne($sql);
            $sql = "select uid from bb_record where id = ".intval($activity_id);
            $uid2 = $db->fetchOne($sql);
            Message::get_instance()
                ->set_title('系统消息')
                ->set_img($pic)
                ->add_content(Message::simple()->content($nickname)->color(0xf4a560) 
                        ->url(json_encode(['type'=>2, 'other_uid'=>$uid ]) )
                        )
                ->add_content(Message::simple()->content('评论了你的视频'))
                ->add_content(Message::simple()->content($title)->color(0xf4a560)  )
                ->set_type(120)
                ->set_uid($uid2)
                ->send();
        }
        
        
        return 1;
    }
    /**
     * 发表回复
     * @param string $table 表名
     * @param string  $content 内容
     * @param int  $comments_id 评论id
     * @param int  $uid 用户ID
     * @return int
     */
    public static function Send_reply_comments($table,$uid,$comments_id,$content)
    {
        $Data = array();
        $Data['content'] = $content;
        $Data['comments_id'] = $comments_id;
        $Data['time'] = time();
        $Data['uid'] = $uid;
        $Data['reply_count'] = 0;
        $Data['audit'] = 0;
        $Data['is_remove'] = 0;
        
        // xieye 2016 自动审核管理员发帖
        $temp = \BBExtend\BBUser::get_user($uid);
        if ($temp && $temp['permissions']==\BBExtend\fix\Permission::bb_user_permission_admin) {
            $Data['audit'] = 1;
        }
        
        Db::table($table)->insert($Data);
        
        // xieye 2016 10
        \BBExtend\user\Tongji::getinstance($uid)->comment();
        
        return 1;
    }
    /**
     * 审核评论
     * @param string $table 表名
     * @param string  $content 内容
     * @param int  $_id 评论id
     * @param string  $audit 1:审核通过 0:未审核 2:审核未通过
     * @return int
     */
    public static function Cer_comments($table,$_id,$audit,$comment_table = '')
    {
        $CommentsDB = Db::table($table)->where('id',$_id)->find();
        if ($CommentsDB)
        {
            if ($audit == 1)
            {
             //   Level::add_user_exp($CommentsDB['uid'],LEVEL_COMMENTS);
            }
            Db::table($table)->where('id',$_id)->update(['audit'=>$audit]);
            if ($comment_table)
            {
                $CommentsDB_array = Db::table($table)->where(['comments_id'=>$CommentsDB['comments_id'],'audit'=>1])->select();
                Db::table($comment_table)->where('$comments_id',$CommentsDB['comments_id'])->update(['reply_count',count($CommentsDB_array)]);
            }
            return 1;
        }
        return 0;
    }
    /**
     * 点赞
     * @param string $table 表名
     * @param int  $_id 评论id
     * @param int  $uid 用户id
     * @param int  $type 1:表示评论 2:表示回复
     * @return array
     */
    public static function _like($table,$_id,$uid,$type)
    {
        $UserDB = Db::table($table)->where(['comments_id'=>$_id,'uid'=>$uid,'type'=>$type])->find();
        if ($UserDB)
        {
            return ['message'=>'您已经点过赞了','code'=>0];
        }
        Db::table($table)->insert(['comments_id'=>$_id,'uid'=>$uid,'type'=>$type]);
        $CommentsDB = self::get_comment_Redis($table,$_id,$type);
        $CommentsDB['like_count']++;
        self::update_comment_Redis($CommentsDB['key'],$CommentsDB);
        
        \BBExtend\user\Tongji::getinstance($uid)->zan();
        
        return ['message'=>'点赞成功','code'=>1];
    }
    /**
     * 取消点赞
     * @param string $table 表名
     * @param int  $_id 评论id
     * @param int  $uid 用户id
     * @param $type 1:表示评论 2:表示回复
     * @return array
     */
    public static function _un_like($table,$_id,$uid,$type)
    {
        $UserDB = Db::table($table)->where(['comments_id'=>$_id,'uid'=>$uid,'type'=>$type])->find();
        if ($UserDB)
        {
            Db::table($table)->where(['comments_id'=>$_id,'uid'=>$uid,'type'=>$type])->delete($UserDB);
            $CommentsDB = self::get_comment_Redis($table,$_id,$type);
            $CommentsDB['like_count']--;
            if ($CommentsDB['like_count']<0)
            {
                $CommentsDB['like_count'] = 0;
            }
            self::update_comment_Redis($CommentsDB['key'],$CommentsDB);
            \BBExtend\user\Tongji::getinstance($uid)->un_zan();
            return ['message'=>'取消成功','code'=>1];
        }
        return ['message'=>'没有找到这条评论数据','code'=>0];
    }
    /**
     * 取得点赞数量
     * @param string $table 表名
     * @param int  $_id 评论id
     * @param int  $uid 用户id
     * @param int  $type 1:表示评论 2:表示回复
     * @return int
     */
    public static function get_likes($table,$_id,$type)
    {
        return (int)self::get_comment_Redis($table,$_id,$type)['like_count'];
    }
    /**
     * 判断用户是否点过赞了
     * @param string $table 表名
     * @param int  $_id 评论id
     * @param int  $uid 用户id
     * @param int  $type 1:表示评论 2:表示回复
     * @return int
     */
    private static function is_like($table,$uid,$_id,$type)
    {
       $CommentDB = Db::table($table)->where(['uid'=>$uid,'comments_id'=>$_id,'type'=>$type])->find();
        if ($CommentDB)
        {
            return 1;
        }
        return 0;
    }
    private static function get_comment_Redis($table,$_id,$type)
    {
        $vid = $table.$_id.$type;
        $CommentDB = BBRedis::getInstance('comments')->hGetAll($vid);
        if (!$CommentDB)
        {
            $CommentDB = array();
            // xieye count
            $CommentDB['like_count'] = Db::table($table)->where(['type'=>$type,
                'comments_id'=>$_id])->count();
            
            $CommentDB['key'] = $vid;
            BBRedis::getInstance('comments')->hMset($vid,$CommentDB);
            return $CommentDB;
        }
        return $CommentDB;
    }
    private static function update_comment_Redis($key,$CommentDB)
    {
        BBRedis::getInstance('comments')->hMset($key,$CommentDB);
    }
}