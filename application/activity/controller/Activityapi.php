<?php
namespace app\activity\controller;
use think\Db;
use BBExtend\BBRedis;
use app\push\controller\Pushmanager;

define('ACTIVITY_TYPE_HEAD',100);//热门
define('ACTIVITY_TYPE_REC',101);//推荐
define('ACTIVITY_TYPE_NEW',102);//最新
define('ACTIVITY_TYPE_FUJIN',103);//附近
class Activityapi
{
   
    //获取玩啥列表

    public function get_activity_list()
    {
        $startid = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        $type = input('?param.type')?(int)input('param.type'):100;
        $address= input('?param.address')?input('param.address'):'';
        $activity_listDB = array();
        switch ($type)
        {
            case ACTIVITY_TYPE_HEAD:
                $activity_listDB = Db::table('bb_activity')->where(['is_remove'=>0])->limit($startid,$length)->order(['heat'=>'desc','people'=>'desc'])->select();
                break;
            case ACTIVITY_TYPE_REC:
                $activity_listDB = Db::table('bb_activity')->where(['is_remove'=>0])->limit($startid,$length)->order(['like'=>'desc'])->select();
                break;
            case ACTIVITY_TYPE_NEW:
                $activity_listDB = Db::table('bb_activity')->where(['is_remove'=>0])->limit($startid,$length)->order(['time'=>'desc'])->select();
                break;
            case ACTIVITY_TYPE_FUJIN:
                //谢烨20160926，修改sql的like
                
                $address = $this->filter_str($address);
                if ($address) {
                  $activity_listDB = Db::table('bb_activity')->where(['address'=>['like','%'.$address."%"],'is_remove'=>0])->limit($startid,$length)->order(['people'=>'desc'])->select();
                }else {
                    return ['data'=>[],'is_bottom'=>1,'code'=>1];
                }
                break;
            default:
                $activity_listDB = Db::table('bb_activity')->where(['label'=>$type,'is_remove'=>0])->limit($startid,$length)->order(['people'=>'desc'])->select();
                break;
        }
        $Data = array();
        foreach ($activity_listDB as $activityDB)
        {
            $activityDB['id'] = (int)$activityDB['id'];
            $activityDB['uid'] = (int)$activityDB['uid'];
            $activityDB['like'] = (int)$activityDB['like'];
            $activityDB['people'] = (int)$activityDB['people'];
            $activityDB['longitude'] = (double)$activityDB['longitude'];
            $activityDB['latitude'] = (double)$activityDB['latitude'];
            $activityDB['is_remove'] = (int)$activityDB['is_remove'];
            $activityDB['is_open'] = (int)$activityDB['is_open'];
            $activityDB['heat'] = (int)$activityDB['heat'];
            $activityDB['time'] = (string)$activityDB['time'];
            $activityDB['is_rmd'] = (int)$activityDB['is_rmd'];
            $activityDB['record_count'] = (int)self::get_count($activityDB['id']);
            //不发送用户列表给客户端
            unset($activityDB['user_group']);
            $ServerURL = \BBExtend\common\BBConfig::get_server_url();
            $bigpic_list = array();
            $pic_list = json_decode($activityDB['bigpic_list'],true);
            foreach ($pic_list as $image)
            {
                if ($image['picpath'] == 'default.jpg')
                {
                    $image['picpath'] = $ServerURL.'/uploads/play_pic/default.jpg';
                }else
                {
                    $image['picpath'] =\BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                            $image['picpath'] );
                    
                }
                array_push($bigpic_list,$image);
            }
            $activityDB['pic'] =\BBExtend\common\PicPrefixUrl::add_pic_prefix_https_use_default(
                    $activityDB['pic'] );
            
            $activityDB['bigpic_list'] = $bigpic_list;
            //xieye 2016 10 优化count
            $activityDB['join_people'] = Db::table('bb_push')->where(['sort'=>3,
                'activity_id'=>$activityDB['id']])->count() 
              + Db::table('bb_rewind')->where(['sort'=>3,
                  'activity_id'=>$activityDB['id']])->count();
              
            $activityDB['score'] = self::get_score_avg($activityDB['id']);
            array_push($Data,$activityDB);
        }
        if (count($activity_listDB) == $length)
        {
            return ['data'=>$Data,'is_bottom'=>0,'code'=>1];
        }
        return ['data'=>$Data,'is_bottom'=>1,'code'=>1];
    }
    //获取某个场馆的所有视频信息
    //delete
    public function get_activity()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $activity_id = input('?param.activity_id')?(int)input('param.activity_id'):0;
        $startid = input('?param.startid')?(int)input('param.startid'):0;
        $length = input('?param.length')?(int)input('param.length'):20;
        $vid=$activity_id.'activity';
        $activityDB = BBRedis::getInstance('activity')->hGetAll($vid);
        if (!$activityDB)
        {
            $activityDB = Db::table('bb_activity')->where('id',$activity_id)->find();
            if ($activityDB)
            {
                BBRedis::getInstance('activity')->hMset($vid,$activityDB);
            }
        }
        if ($activityDB)
        {
            $pushManager = new Pushmanager();
            $PushList = $pushManager->get_show_list($uid,3,$startid,$length,$activity_id);
            if (count($PushList) == $length)
            {
                return ['data'=>$PushList,'is_bottom'=>0,'code'=>1];
            }else
            {
                return ['data'=>$PushList,'is_bottom'=>1,'code'=>1];
            }
        }
        return ['message'=>'非法的活动ID！','code'=>0];
    }
    //活动评论平均分数
    private static function get_score_avg($activity_id)
    {
        $CommentsDB_avg = Db::table('bb_activity_comments')->where(['activity_id'=>$activity_id,'audit'=>1,'is_remove'=>0])->avg('score');
        if ($CommentsDB_avg)
        {
            return (int)$CommentsDB_avg;
        }
        return 0;
    }

    //获得活动录制视频数量
    private static function get_count($activity_id)
    {
        //xieye
       return Db::table('bb_record')->where(['type'=>1,'usersort'=>3,'audit'=>1,
           'is_remove'=>0,'activity_id'=>$activity_id])->count();
    }
    //谢烨20160926 ，过滤like
    private  function filter_str($s)
    {
        //先把换行改成空格
        $pattern = '/(\r\n|\n)/';
        $s = preg_replace($pattern, '', $s);
        //20-7e 包括了0－9a-zA-Z空格，英文标点。是ascii表的主要一部分
        // 4e00- 9fa5 全部汉字，但不含中文标点
        $pattern = '/[^\x{4e00}-\x{9fa5}]/u';
        $s = preg_replace($pattern, '', $s);
        return $s;
    }
    
}
?>