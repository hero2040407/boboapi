<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_request (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  url         varchar(400) not null default '' comment '网址',
  create_time int          not null default 0  comment '创建时间',
  ip          varchar(255) not null default '' comment 'ip',
  version     varchar(255) not null default '' comment '版本号',      
  PRIMARY KEY (id),
  index url (url)
) ENGINE=MyISAM DEFAULT CHARSET=utf8     
html;
Db::query($sql);


echo "创建<br>\n";
