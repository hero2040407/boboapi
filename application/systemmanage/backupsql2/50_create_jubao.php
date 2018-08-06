<?php

/**
 * 创建举报日志表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_jubao_log (
  id int NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int NOT NULL DEFAULT 0 COMMENT '用户ID',
  type tinyint not null default 0 comment '1禁止直播，2禁止发言',
  count int NOT NULL DEFAULT 0 COMMENT '举报次数',
  create_time int NOT NULL DEFAULT 0 COMMENT '生成时间',
  is_complete tinyint not null default 0 comment '0未处理，1已处理',
  PRIMARY KEY (id),
  KEY uid (uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 comment "举报日志表"       
html;
Db::query($sql);


echo "创建举报日志<br>\n";
