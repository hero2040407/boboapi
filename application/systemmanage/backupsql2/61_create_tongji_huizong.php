<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_tongji_huizong (
  id int NOT NULL AUTO_INCREMENT COMMENT '主键',
  datestr int not null default '20160101' comment '统计日期，格式类似20161001，固定8位',
  zhibo_shichang int not null default 0 comment '用户当日平均直播时长，单位秒',
  shipin_count   int not null default 0 comment '用户当日上传视频数',
  pinglun_count  int not null default 0 comment '用户当日评论总数',
  huodong_count  int not null default 0 comment '用户参与活动次数',
  renzheng_user_count int not null default 0 comment '用户当日认证数',
  vip_count      int not null default 0 comment '用户当日购买vip数',
  renzheng_shipin_count int not null default 0 comment '用户当日认证视频数',      
  view_shipin_count     int not null default 0 comment '当日观看视频人数总数',
  PRIMARY KEY (id),
  index datestr(datestr)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 comment "统计汇总表"       
html;
Db::query($sql);


echo "创建<br>\n";
