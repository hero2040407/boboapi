<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
CREATE TABLE bb_aliyun_kill_log (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  stream_name varchar(255) not null default '' comment '流名称',
  domain_name   varchar(255) not null default '' comment '域名',
  create_time datetime comment '发生时间',
  is_huifu tinyint not null default 0 comment '0被禁止，1又被恢复直播',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY stream_name(stream_name),
  KEY domain_name (domain_name)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='阿里云禁止直播推流记录'
html;
Db::query($sql);




echo "创建<br>\n";
