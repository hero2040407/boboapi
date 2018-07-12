<?php
namespace app\live_device\controller;
use app\user\controller\User;
use BBExtend\BBRedis;
use think\Db;

define('ACTIVITY_TYPE_HEAD',100);//热门
define('ACTIVITY_TYPE_REC',101);//推荐
define('ACTIVITY_TYPE_NEW',102);//最新
define('ACTIVITY_TYPE_FUJIN',103);//附近
class Api
{
    public function __construct()
    {
        
        return NULL;
    }

    //设备登录
    public function device_login()
    {
        $device_key= input('?param.device_key')?(string)input('param.device_key'):'';
        $Device_IP = input('?param.device_ip')?(string)input('param.device_ip'):'';
        $device_type = input('?param.device_type')?(string)input('param.device_type'):'';
        $DeviceDB = Db::table('bb_live_device')->where(['device_key'=>$device_key])->find();
        if ($DeviceDB)
        {
            Db::table('bb_live_device')->where(['device_key'=>$device_key])->update(['online'=>1]);
            return ['uid'=>$DeviceDB['uid'],'code'=>1];
        }
        else
        Db::table('bb_live_device')->insert(['device_key'=>$device_key,'device_ip'=>$Device_IP,'device_type'=>$device_type,'online'=>1]);
        return ['uid'=>null,'code'=>1];
    }

    //设备退出
    public function device_exit()
    {
        $device_key= input('?param.device_key')?(string)input('param.device_key'):'';
        $DeviceDB = Db::table('bb_live_device')->where(['device_key'=>$device_key])->find();
        if ($DeviceDB)
        {
            Db::table('bb_live_device')->where(['device_key'=>$device_key])->update(['online'=>0]);
            return ['uid'=>$DeviceDB['uid'],'code'=>1];
        }
        return ['message'=>'没有这个设备','code'=>0];
    }
    
    //绑定设备
    public function bind_device()
    {
        $device_key= input('?param.device_key')?(string)input('param.device_key'):0;
        $uid= input('?param.uid')?(int)input('param.uid'):0;
        $DeviceDB = Db::table('bb_live_device')->where(['device_key'=>$device_key])->find();
        if ($DeviceDB&!$DeviceDB['uid'])
        {
            $DeviceDB['uid'] = $uid ;
            Db::table('bb_live_device')->where(['device_key'=>$device_key])->update($DeviceDB);
            return ['message'=>'绑定成功','code'=>1];
        }
        return ['message'=>'绑定失败','code'=>0];
    }
    //请求推流的连接
    public function request_push_url()
    {
        $uid = input('?param.uid') ?(int) input('param.uid') : '';
        if (\app\user\model\Exists::userhExists($uid)!=1)
        {
            return ['code'=>-1];
        }
        $bigpic = User::get_user_pic_no_http($uid);
        $PushDB = Db::table('bb_push')->where('uid',$uid)->find();
        $PushName = md5(time().$uid);
        if (!$PushDB)
        {
            $PushDB =  array();
            $PushDB['push_url'] = 'rtmp://PushLive.yimwing.com/5showcam/'.$PushName;
            $PushDB['pull_url'] = 'http://hlives.yimwing.com/5showcam/'.$PushName.'/playlist.m3u8';
            $PushDB['space_name'] = '5showcam';
            $PushDB['stream_name'] = $PushName;
            $PushDB['uid'] = $uid;
            $PushDB['sort'] = 2;
            $PushDB['bigpic'] = $bigpic;
            $PushDB['room_id'] = $uid.'push';
            $PushDB['activity_id'] = 0;
            $PushDB['event'] = 'publish_done';
            $PushDB['flowers'] = 0;
            $PushDB['like'] = 0;
            Db::table('bb_push')->where('uid',$uid)->insert($PushDB);
            BBRedis::getInstance('push')->hMset($uid.'push',$PushDB);
        }else
        {
            $PushDB['push_url'] = 'rtmp://PushLive.yimwing.com/5showcam/'.$PushName;
            $PushDB['pull_url'] = 'http://hlives.yimwing.com/5showcam/'.$PushName.'/playlist.m3u8';
            $PushDB['space_name'] = '5showcam';
            $PushDB['stream_name'] = $PushName;
            $PushDB['uid'] = $uid;
            $PushDB['sort'] = 2;
            $PushDB['bigpic'] = $bigpic;
            $PushDB['room_id'] = $uid.'push';
            $PushDB['like'] = 0;
            $PushDB['people'] = 0;
            $PushDB['activity_id'] = 0;
            $PushDB['event'] = 'publish_done';
            Db::table('bb_push')->where('uid',$uid)->update($PushDB);
            BBRedis::getInstance('push')->hMset($uid.'push',$PushDB);
        }
        $Data = array();
        $Data['push_url'] = 'rtmp://PushLive.yimwing.com/5showcam/'.$PushName;
        $Data['pull_url'] = 'http://hlives.yimwing.com/5showcam/'.$PushName.'/playlist.m3u8';
        $Data['space_name'] = '5showcam';
        $Data['stream_name'] = $PushName;
        $Data['token'] = base64_encode($PushName);
        return ['data'=>$Data,'code'=>1];
    }
    //结束推流
    
}
?>