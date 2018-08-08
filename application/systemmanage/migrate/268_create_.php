<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
CREATE TABLE bb_users_achievement_summary  (
        
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int not null default 0 comment '用户id',      
  
  dengji int not null default 0 comment '等级',
  zhibo int not null default 0 comment '直播时长，单位秒',
  pinglun int not null default 0 comment '评论次数，要审核',
  dianzan int not null default 0 comment '点赞次数',
  zhubo int not null default 0 comment '被点赞次数',
  
  hongren int not null default 0 comment '粉丝数',
  huodong int not null default 0 comment '参加活动次数',
  dasai int not null default 0 comment '参加大赛次数，要审核',
  neirong int not null default 0 comment '短视频发布次数，要审核',
        
  PRIMARY KEY (id),
  KEY uid(uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 comment='用户成就汇总表'
html;
Db::query($sql);







echo "创建<br>\n";
