<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_user_weixin_push_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  create_time datetime comment '推送时间',
  ip varchar(255) not null default '' comment 'ip',
  type varchar(255) not null default '' comment '推送事件，例如关注，取关',
  openid  varchar(255) not null default '' comment 'openid',
  info varchar(1000)  not null default '' comment '内容',
  PRIMARY KEY (id),
  index type(type),
  index openid(openid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 comment "微信推送日志，主要用于测试"  
html;
Db::query($sql);








echo "创建<br>\n";
