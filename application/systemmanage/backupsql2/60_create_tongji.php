<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_tongji_log (
  id int NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int not null default 0 comment '用户id',
  data   int     not null default 0 comment '数据',
  data2  int not null default 0 comment '数据2',
  info  varchar(1000)     not null default '' comment '备注',
  type    tinyint not null default 1 comment 
        '1直播开始时间，2直播结束时间，3用户上传视频，4评论，5观看直播数，6认证人数，7活动次数',
  PRIMARY KEY (id),
  index uid(uid),
  index type(type)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 comment "统计日志表"       
html;
Db::query($sql);


echo "创建<br>\n";
