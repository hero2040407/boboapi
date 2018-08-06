<?php
namespace app\apptest\controller;

use BBExtend\BBRedis;
use  think\Db;
use BBExtend\Sys;
use BBExtend\Currency;


class Data
{
    public function test(){
        
        echo  e(123);
        
        $db = Sys::get_container_db();
        $sql="select * from bb_area limit 2";
        $result = $db->fetchAll($sql);
        dump($result);
        
        $models = \BBExtend\model\Area::where("id",">","1")->orderBy("id","desc")->take(2)->get();
        echo  ($models->toJson(JSON_UNESCAPED_UNICODE));
        $dbe = Sys::getdb_eloquent();
        $users = $dbe::select ( 'SELECT * FROM bb_area limit 1' );
        dump($users);
    }
    
    /**
     * CREATE TABLE bb_group (
  id int(11) NOT NULL AUTO_INCREMENT,
  type        tinyint      NOT NULL DEFAULT 0 COMMENT '1微信群，2qq群',
  code        varchar(255) NOT NULL DEFAULT '' COMMENT '建群用的微信号或qq号',
  bb_type     tinyint      not null default 0 comment '1邀约群，2大赛群',
  ds_id       int          NOT NULL DEFAULT 0 COMMENT '仅限大赛群，表示大赛id',
  title       varchar(255) NOT NULL DEFAULT '' COMMENT '群名称',
  pic         varchar(255) NOT NULL DEFAULT '' COMMENT '群图标，单纯显示用',
  qrcode_pic  varchar(255) NOT NULL DEFAULT '' COMMENT '二维码图标，app可以调起加群请求',
  create_time int          not null default 0 comment '创建时间',
  update_time int          not null default 0 comment '最后修改时间',
  PRIMARY KEY (id),
  KEY bb_type (bb_type),
  KEY ds_id (ds_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='聊天群表'

     */
    public function group()
    {
        $s=<<<sql
bb_group:
  -
    id: 1
    type: 1
    bb_type: 1
    title: 谢烨微信群
    pic: /public/pic/resource/1.png
    qrcode_pic: /public/temp/wx_erwei.png
  -
    id: 2
    type: 2
    bb_type: 1
    title: 谢烨qq群
    pic: /public/pic/resource/2.png
    qrcode_pic: /public/temp/qq_erwei.png
                
sql;
      \BBExtend\common\MysqlTool::populate_by_yaml($s);
        echo "ok";
        $db = Sys::get_container_db();
        $sql="select count(*) from bb_group";
        $count = $db->fetchOne($sql);
        echo "已插入{$count}条记录";
    }
   
   
    
    /**
     * 这是删除用户，无效的，勿删
     */
    public function removeuser(){
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql ="
   select  cc.* from bb_users cc where exists (select 1  from (
        select phone,count(*) from bb_users where pic like 'http://bobo-upload.oss-cn-beijing.aliyuncs.com%'  
         group by phone having count(*)=2  )a
         where a.phone = cc.phone )
order by cc.phone asc             
                ";
        $stat = $db2->query($sql);
        $i=0;
        while($row= $stat->fetch()) {
            
            if ($row['address']=='未设定') {
                
            //    \BBExtend\user\Remove::getinstance($row['uid'])->del();
                
                $i++;
                echo $i ." === " . $row['uid']."\n";
                //if ($i > 10) break;
            }
        }
    }
    
    
    
    
    //这是做假数据，配合新的假用户，误删除。
    public function currency(){
        $db = Sys::get_container_db();
        $db2 = Sys::getdb2();
        $sql ="
   select bb_users.uid from bb_users where not exists (select 1 from 
  bb_currency where bb_currency.uid = bb_users.uid
)
order by bb_users.uid asc
                ";
        $stat = $db2->query($sql);
        $i=0;
        while($row= $stat->fetch()) {
            $uid = $row["uid"];
            \BBExtend\Currency::get_currency($uid);
            \BBExtend\Level::get_user_exp($uid);
            echo $i++ ." === " . $row['uid']."\n";
//             if ($row['address']=='未设定') {
    
//                 \BBExtend\user\Remove::getinstance($row['uid'])->del();
    
//                 $i++;
//                 echo $i ." === " . $row['uid']."\n";
//                 //if ($i > 10) break;
//             }
        }
    }
    
       
}
