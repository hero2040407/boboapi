<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
CREATE TABLE bb_tongji_user_huizong (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int not null default 0 comment '用户id',
  qudao varchar(255) not null default '' comment '渠道',
  datestr int(11) NOT NULL DEFAULT '20160101' COMMENT '统计日期，格式类似20161001，固定8位',
  zhibo_time int(11) NOT NULL DEFAULT '0' COMMENT '用户当日直播总时长，单位秒',
  shipin_count int(11) NOT NULL DEFAULT '0' COMMENT '用户当日上传视频总数',
  pinglun_count int(11) NOT NULL DEFAULT '0' COMMENT '用户当日评论总数',
  huodong_count int(11) NOT NULL DEFAULT '0' COMMENT '用户参与活动次数',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  online_time int(11) NOT NULL DEFAULT '0' COMMENT '当日总在线时长，秒',
  view_zhibo_time int(11) NOT NULL DEFAULT '0' COMMENT '当日看直播总时长，秒',
  view_record_count int(11) NOT NULL DEFAULT '0' COMMENT '当日看短视频次数',
  PRIMARY KEY (id),
  index datestr (datestr),
  index uid(uid),
  index qudao(qudao)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='单个用户每日统计汇总表'        
";
Db::query($sql);







echo "创建<br>\n";
