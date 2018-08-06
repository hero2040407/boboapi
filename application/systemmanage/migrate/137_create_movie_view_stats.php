<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_moive_view_stats (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid  int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  type tinyint not null default 0 comment '对应短视频表的type',
  usersort   int not null default 0 comment '对应短视频的usersort',
  view_count int not null default 0 comment '观看次数',      
  PRIMARY KEY (id),
  key uid (uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户观看短视频喜好类型统计表'
html;
Db::query($sql);


echo "创建<br>\n";
