<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_msg_push_log (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '消息接收者',
  info varchar(1000) NOT NULL DEFAULT '' COMMENT '消息内容',
  type int not null default 0 comment '消息类型',
  create_time int not null default 0 comment '创建时间',
  datetimestr datetime not null default '1970-01-01' comment '创建时间',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY type (type)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='消息推送日志表'   
html;
Db::query($sql);






echo "创建<br>\n";
