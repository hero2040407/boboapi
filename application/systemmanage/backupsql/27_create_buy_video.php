<?php

/**
 * 创建视频购买表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_buy_video (
  id int not null AUTO_INCREMENT comment '主键',
  uid int(11) NOT NULL  default 0  COMMENT '用户ID',
  video_id int not null default 0 comment '视频id，注意有3张表',
  video_table varchar(255) not null default '' comment 'bb_push直播表，bb_record录播表，',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '生成时间',
  price int not null default 0 comment '波币价格',
  PRIMARY KEY (id),
  index uid(uid),
  index video_id (video_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
html;
Db::query($sql);

echo "创建视频购买表<br>\n";

