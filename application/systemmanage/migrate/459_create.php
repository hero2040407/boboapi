<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE   bb_users_talent (
  id       int NOT NULL AUTO_INCREMENT,
  uid    int NOT NULL DEFAULT '0' COMMENT '人才id',
  create_time int not null default 0 comment '创建时间',
  PRIMARY KEY (id),
  index uid(uid)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='艺术人才库'
html;
Db::query($sql);



echo "创建<br>\n";

