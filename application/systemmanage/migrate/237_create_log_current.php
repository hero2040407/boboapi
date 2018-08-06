<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_tongji_log_today (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  data int(11) NOT NULL DEFAULT '0' COMMENT '数据',
  data2 int(11) NOT NULL DEFAULT '0' COMMENT '数据2',
  info varchar(1000) NOT NULL DEFAULT '' COMMENT '备注',
  type tinyint(4) NOT NULL DEFAULT '1' COMMENT '1直播开始时间，2直播结束时间，3用户上传视频，4评论，5观看直播数，6认证人数，7活动次数',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  datestr varchar(255) NOT NULL DEFAULT '' COMMENT '类似20160101',
  money decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额统计',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY type (type),
  KEY datestr (datestr),
  KEY info (info)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='统计日志表,仅限今日'   
html;
Db::query($sql);






echo "创建<br>\n";
