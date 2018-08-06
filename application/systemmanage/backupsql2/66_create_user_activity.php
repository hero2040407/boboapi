<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_user_activity (
  id int NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid         int not null default 0 comment '用户id',
  activity_id int not null default 0 comment '活动id',
  create_time int not null default 0 comment '创建时间',
  PRIMARY KEY (id),
  index uid(uid),
  index activity_id(activity_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 comment "用户参加活动表"       
html;
Db::query($sql);


echo "创建<br>\n";
