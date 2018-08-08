<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_msg_answer (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  msg_id int(11) NOT NULL DEFAULT '0' COMMENT '原来的消息id，对应bb_msg的主键',
  info varchar(1000) NOT NULL DEFAULT '' COMMENT '用户提交的内容',
  uid int not null default 0 comment '用户id',
  type int not null default 0 comment '消息类型',
  datestr char(8) not null default '' comment '回复时间的年月日表示',
  create_time int not null default 0 comment '回复时间',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY type (type),
  KEY datestr (datestr)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='系统消息用户回复表'   
html;
Db::query($sql);






echo "创建<br>\n";
