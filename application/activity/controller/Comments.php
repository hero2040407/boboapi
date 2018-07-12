<?php
/**
 * Created by PhpStorm.
 * User: CY
 * Date: 2016/8/18
 * Time: 12:39
 */

namespace app\activity\controller;


use BBExtend\BBComments;

class Comments extends BBComments
{
    //获得评论列表
    public function get_comments_list()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $is_uid = \app\user\model\Exists::userhExists($uid);
        if ($is_uid!=1)
        {
            return ['message'=>'没有这个用户','code'=>$is_uid];
        }
        $activity_id = input('?param.activity_id')?(int)input('param.activity_id'):0;
        $start_id = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        $ListDB = self::Get_comments('bb_activity_comments',$activity_id,$start_id,$length,'bb_activity_comments_like',$uid);
        if (count($ListDB) == $length)
        {
            return ['data'=>$ListDB,'comments_count'=>self::Get_comments_count('bb_activity_comments',$activity_id),'is_bottom'=>0,'code'=>1];
        }
        else
        {
            return ['data'=>$ListDB,'comments_count'=>self::Get_comments_count('bb_activity_comments',$activity_id),'is_bottom'=>1,'code'=>1];
        }
    }
    //获得回复评论列表
    public function get_comments_reply_list()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $is_uid = \app\user\model\Exists::userhExists($uid);
        if ($is_uid!=1)
        {
            return ['message'=>'没有这个用户','code'=>$is_uid];
        }
        $comments_id = input('?param.comments_id')?(int)input('param.comments_id'):0;
        $start_id = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        $ListDB = self::Get_reply_comments('bb_activity_comments_reply',$comments_id,$start_id,$length,'bb_activity_comments_like',$uid);
        if (count($ListDB) == $length)
        {
            return ['data'=>$ListDB,'is_bottom'=>0,'code'=>1];
        }
        else
        {
            return ['data'=>$ListDB,'is_bottom'=>1,'code'=>1];
        }
    }
    //对活动进行评论
    public function comments()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $is_uid = \app\user\model\Exists::userhExists($uid);
        if ($is_uid!=1)
        {
            return ['message'=>'没有这个用户','code'=>$is_uid];
        }
        $activity_id = input('?param.activity_id')?(int)input('param.activity_id'):0;
        $content = input('?param.content')?(string)input('param.content'):'';
        $score = input('?param.score')?(int)input('param.score'):0;
        return ['code'=>self::Send_comments('bb_activity_comments',$uid,$activity_id,$content,$score)];
    }
    //回复评论
    public function reply()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $is_uid = \app\user\model\Exists::userhExists($uid);
        if ($is_uid!=1)
        {
            return ['message'=>'没有这个用户','code'=>$is_uid];
        }
        $comments_id = input('?param.comments_id')?(int)input('param.comments_id'):0;
        $content = input('?param.content')?(string)input('param.content'):'';
        return ['code'=>self::Send_reply_comments('bb_activity_comments_reply',$uid,$comments_id,$content)];
    }
    //评论点赞
    public function comments_like()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $id = input('?param.id')?(int)input('param.id'):0;
        return self::_like('bb_activity_comments_like',$id,$uid,1);
    }
    //取消评论点赞
    public function comments_un_like()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $id = input('?param.id')?(int)input('param.id'):0;
        return self::_un_like('bb_activity_comments_like',$id,$uid,1);
    }
    //回复点赞
    public function reply_like()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $id = input('?param.id')?(int)input('param.id'):0;
        return self::_like('bb_activity_comments_like',$id,$uid,2);
    }
    //取消回复点赞
    public function reply_un_like()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $id = input('?param.id')?(int)input('param.id'):0;
        return self::_un_like('bb_activity_comments_like',$id,$uid,2);
    }
}