<?php
namespace BBExtend;

/**
 * Created by PhpStorm.
 * 粉丝类
 * User: CY
 * Date: 2016/7/20
 * Time: 11:43
 */
use think\Db;
class Focus
{
   
    //得到粉丝
    public static function get_focus_user($uid,$startid,$length)
    {
        $focus_array = Db::table('bb_focus')->where('focus_uid',$uid)->order('time','desc')-> limit($startid,$length)->select();
        return $focus_array;
    }


//     public static function focus($self_uid,$focus_uid)
//     {
//         if (self::get_focus_state($self_uid,$focus_uid))
//         {
//             return false;
//         }else
//         {
//             Db::table('bb_focus')->insert(['uid'=>$self_uid,'focus_uid'=>$focus_uid,'time'=>time()]);
//             //增加被点赞人的经验
//             Level::add_user_exp($focus_uid,LEVEL_LIKE);
//             return true;
//         }
//     }

    // xieye 2016 10 24

    //判断我是否关注过这个人
    public static function get_focus_state($self_uid,$focus_uid)
    {
        $result = \BBExtend\user\Focus::getinstance($self_uid)->has_focus($focus_uid);
        return boolval($result);
        
//         $focusDB = Db::table('bb_focus')->where(['uid'=>$self_uid,'focus_uid'=>$focus_uid])->find();
//         if ($focusDB)
//         {
//             return true;
//         }
//         return false;
    }
//     public static function un_focus($self_uid,$focus_uid)
//     {
//         $focusDB = Db::table('bb_focus')->where(['uid'=>$self_uid,'focus_uid'=>$focus_uid])->find();
//         if ($focusDB)
//         {
//             Db::table('bb_focus')->where(['uid'=>$self_uid,'focus_uid'=>$focus_uid])->delete();
//             return true;
//         }
//         return false;
//     }
}