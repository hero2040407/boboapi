<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_user_hobby (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  hobby_id int(11) NOT NULL DEFAULT '0' COMMENT '兴趣id',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '当前时间',
  PRIMARY KEY (id),
  KEY hobby_id (hobby_id),
  key uid (uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='个人兴趣表'
html;
Db::query($sql);


echo "创建<br>\n";
