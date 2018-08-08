<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
CREATE TABLE bb_users_starmaker (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  create_time int not null default 0 comment '创建时间',
  PRIMARY KEY (id),
  KEY uid (uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='星推官表'
html;
Db::query($sql);




echo "创建<br>\n";
