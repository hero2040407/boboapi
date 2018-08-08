<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_client_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  ip varchar(255) not null default '' comment 'ip',
  datestr char(8) not null default '' comment '类似20170101',
  agent varchar(255) not null default '' comment '代理',
  content text  COMMENT '错误日志',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '当前时间',
  PRIMARY KEY (id),
  key datestr(datestr),
  key ip(ip)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='客户端错误日志表'
html;
Db::query($sql);



echo "创建<br>\n";
