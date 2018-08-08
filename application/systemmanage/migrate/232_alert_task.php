<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_system_task (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  datestr int(11) NOT NULL DEFAULT '20160101' COMMENT '创建日期，格式类似20161001，固定8位',
  type tinyint not null default 0 comment '1,假的关注',
  uid int not null default 0 comment 'uid',
  target_uid   int not null default 0 comment '目标uid',
  info varchar(255) not null default '' comment '信息',
  json_str varchar(800) not null default '' comment 'json字符串',
  created_at int not null default 0 comment '创建时间',
  task_at int not null default 0 comment '任务应该在什么时候完成',
  has_finish tinyint not null default 0 comment '1已完成 ，0没有',
  PRIMARY KEY (id),
  KEY datestr (datestr),
  key uid (uid),
  key target_uid(target_uid),
  key type(type)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='定时任务表'
html;
Db::query($sql);


echo "创建<br>\n";
