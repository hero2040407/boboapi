<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_msg_cache (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  uid int(11) not null default 0  comment '消息接收者 ',
  type smallint(2) DEFAULT '0' COMMENT '消息类型',
  title varchar(50) not null default '' comment '标题',
  info varchar(1024) not null default '' comment '内容',
  img varchar(255)  not null default '' comment '图片',
  time int  not null default 0  comment '发生时间 ',
  is_read smallint(1) not null DEFAULT '0' COMMENT '是否已经读取',
  overdue_time varchar(11) not null  DEFAULT '' COMMENT '过期时间',
  other_uid int not null  default 0 comment '对方uid',
  PRIMARY KEY (id),
  KEY uid (uid),
  key other_uid(other_uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 
html;
Db::query($sql);






echo "创建<br>\n";
