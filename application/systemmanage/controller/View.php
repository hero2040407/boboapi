<?php
namespace app\systemmanage\controller;
/**
 * 
 * 
 * @author 谢烨
 */


use BBExtend\Sys;
use BBExtend\common\HtmlTable;
use BBExtend\common\Numeric;
class View 
{ 
    /**
     * xieye:
     * 该程序轻易通过web让管理员查看哪些视频正在直播。
     */
    public function push()
    {
        $db = Sys::get_container_db();
        echo "<title>怪兽bobo - 检测所有在线的直播</title>";
        echo "<h2>怪兽bobo - 所有在线的直播</h2>";
        echo "说明：
       <ol> 
   <li> 以下列表是阿里云所有正在直播的视频列表。其中publish表示我们网站标记其正在直播，publish_done表示我们网站标记其为不直播。</li>
       <li>出现publis_done表示主播下线，但阿里云反应较慢，没有及时纠正，但我们自己已经在数据库中将其下线，不会显示在直播列表中。</li>
       </ol>        
                
                ";
        
        $domain_arr=["www.yimwing.com"];
        foreach (range(1,19) as $id  ) {
            $domain_arr[]= "push{$id}.yimwing.com";
        }
        foreach ($domain_arr as $domain) {
             
            $result = \BBExtend\aliyun\Common::describeLiveStreamsOnlineList($domain);
            ini_set('date.timezone','Asia/Shanghai');
            foreach ($result as $v) {
                $sql="select bb_users.uid,bb_users.nickname,event,stream_name ,
                        bb_push.time
                        from bb_push
                       left join bb_users
                        on bb_users.uid=bb_push.uid
                       where stream_name=?";
                $push = $db->fetchRow($sql, $v);
                $user = \app\user\model\UserModel::getinstance($push['uid']);
                $pic = $user->get_userpic();
                $nickname = $user->get_nickname();
                echo "<p><img width=50 height=50 src='{$pic}' /> " . $nickname. " ".$push["event"] ." ".
                  "开始直播时间：". date("Y-m-d H:i:s", $push['time']+8*3600) ."</p>";
            }
            flush();
            ob_flush();
        }
    }
    
    public function index($pass='')
    {
        if ($pass != '123456') {
            echo "密码错误";
            exit();
        }
        
        
        $db = Sys::get_container_db();
        echo "<h2>运营统计</h2>";
        
        $sql ="select id, datestr, register_count,login1_count,
                
                login2_count,login3_count,login4_count,
                
                online_time,
                movie_view_count_today,
                movie_view_count_all,
                movie_view_avg_today,
                movie_view_avg_all,
                
                
                push_view_count_today,
                
                push_view_avg_today,
                push_view_avg_all
               
                
        
                from  bb_tongji_huizong order by datestr desc limit 30";
        $result = $db->fetchAll($sql);
        
        foreach ($result as &$v) {
            $v['online_time'] = Numeric::div($v['online_time'], 60)."分"  ;
            //             $v['create_time'] = date("Y-m-d H:i:s") ;
        }
        
        $obj = new HtmlTable(
                array('id','日期','注册数','次日登录数',
                    '3日登录数','7日登录数','7日二回登录数',
                    
                    '平均在线时长',
                    '当日视频浏览数',
                    '总视频浏览数',
                    '当日平均视频浏览数',
                    '总平均视频浏览数',
                    
                    '当日看直播次数',
                    
                    '当日平均观看直播时长',
                    '总平均观看直播时长',
                    
        
                ),$result
         );
        
        echo $obj->to_html();
        echo "<br><br>";
        $sql ="select id, datestr, zhibo_shichang,
                shipin_count,pinglun_count,
                huodong_count,
                renzheng_user_count,
                vip_count,
                renzheng_shipin_count,
                view_shipin_count,
                share_count
                
                from  bb_tongji_huizong order by datestr desc limit 30";
        $result = $db->fetchAll($sql);
        
        foreach ($result as &$v) {
            $v['zhibo_shichang'] = Numeric::div($v['zhibo_shichang'], 60)."分"  ;
            //             $v['create_time'] = date("Y-m-d H:i:s") ;
        }
        
        $obj = new HtmlTable(
                array('id','日期','平均直播时长','用户上传视频数',
                    '用户评论数',
                    '用户参与活动次数',
                    '每日认证用户数',
                    'vip购买次数',
                    '认证视频数',
                    '观看直播数',
                    '当日分享次数',
                    //                     '统计时间',
        
                ),$result
                );
        
        echo $obj->to_html();
        
        
        
    }
    
}