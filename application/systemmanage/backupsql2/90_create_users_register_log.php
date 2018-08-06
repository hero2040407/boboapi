<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
CREATE TABLE bb_users_register_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  platform_id varchar(255) not null default '' comment '平台id',
  login_type tinyint not null default 0 comment '1： 微信 2：QQ  3：手机 4：微博', 
  ip varchar(255) not null default '' comment 'ip',
  create_time int not null default 0 comment '注册时间',
  model varchar(255) not null default '' comment '机型',
  qudao varchar(255) not null default '' comment '渠道',
  datestr varchar(255) not null default '' comment '纯日期，类似20161102',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY datestr (datestr),
  key qudao(qudao),
  key model (model),
  key ip(ip)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='注册日志表'        
";
Db::query($sql);






echo "创建<br>\n";
