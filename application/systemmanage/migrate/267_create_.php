<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
CREATE TABLE bb_config_str (
  id int(11) NOT NULL AUTO_INCREMENT,
  config varchar(255) NOT NULL DEFAULT '' COMMENT '键，一般用英文或拼音，成就的键类似huodong0,',
  val varchar(255) NOT NULL DEFAULT '' COMMENT '值，任意文字',
  type tinyint not null default 0 comment '1代表成就图标',
  PRIMARY KEY (id),
  KEY config (config),
  key type(type)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 comment='系统配置表，值为字符串'
html;
Db::query($sql);







echo "创建<br>\n";
