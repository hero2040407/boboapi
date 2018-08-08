<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
CREATE TABLE bb_request2 (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  url varchar(400) NOT NULL DEFAULT '' COMMENT '网址',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  ip varchar(255) NOT NULL DEFAULT '' COMMENT 'ip',
  version varchar(255) NOT NULL DEFAULT '' COMMENT '版本号',
  user_agent varchar(500) NOT NULL DEFAULT '' COMMENT '头信息user_agent',
  datestr char(8) NOT NULL DEFAULT '' COMMENT '日期',
  domain varchar(255) NOT NULL DEFAULT '' COMMENT '域名',
  duration int(11) NOT NULL DEFAULT '0' COMMENT '程序用时',
  post varchar(2000) NOT NULL DEFAULT '' COMMENT 'post的json数组',
  PRIMARY KEY (id),
  KEY url (url(333))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8

html;
Db::query($sql);






echo "创建<br>\n";

