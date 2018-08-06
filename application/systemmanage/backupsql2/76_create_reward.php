<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_user_activity_reward (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  activity_id int(11) NOT NULL DEFAULT '0' COMMENT '活动id',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  has_reward tinyint(4) NOT NULL DEFAULT '0' COMMENT '1已领奖，0未领奖',
  reward_count int(11) NOT NULL DEFAULT '0' COMMENT '领取的波币数',
  reward_time int(11) NOT NULL DEFAULT '0' COMMENT '领奖时间',
  paiming int(11) NOT NULL DEFAULT '0' COMMENT '排名，1最大',
  record_id int(11) NOT NULL DEFAULT '0' COMMENT '短视频id',
  room_id varchar(255) NOT NULL DEFAULT '0' COMMENT '房间id',
  like_count int(11) NOT NULL DEFAULT '0' COMMENT '赞数',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY activity_id (activity_id),
  KEY record_id (record_id),
  KEY room_id (room_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户参加活动奖励表'     
html;
Db::query($sql);


echo "创建<br>\n";
