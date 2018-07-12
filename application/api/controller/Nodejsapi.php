<?php
/**
 * Created by PhpStorm.
 * User: tRee
 * Date: 2016/7/7
 * Time: 19:37
 */
namespace app\api\controller;
use BBExtend\BBRedis;
use think\Db;


class Nodejsapi 
{
    public function clear()
    {
        BBRedis::getInstance('push')->flushAll();
        return ['code' => 1, 'message' => '清除成功!'];
    }

    public function get_push_db()
    {

    }
    public function exit_push()
    {
        $uid = input('?param.uid') ?(string) input('param.uid') : '';
        $flowers = input('?param.flowers') ?(string) input('param.flowers') : '';
        Db::table('bb_push')->where(['uid'=>$uid])->update(['event'=>'publish_done','flowers'=>$flowers]);
        BBRedis::getInstance('push')->hSet($uid.'push','event','publish_done');
        BBRedis::getInstance('push')->hSet($uid.'push','flowers',$flowers);
        return ['code'=>1];
    }
}
