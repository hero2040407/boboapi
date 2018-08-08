<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_users_test (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL DEFAULT '0' COMMENT '测试用户uid',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '时间',
  PRIMARY KEY (id),
  KEY uid (uid)
) ENGINE=MyISAM   COMMENT='测试用户表'
html;
Db::query($sql);



echo "创建<br>\n";
