<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_tongji_user_login_time (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL DEFAULT '0' COMMENT '当前用户',
  datestr varchar(255) NOT NULL DEFAULT '' COMMENT '，日期，类似2016-01-12',
  dateint int(11) NOT NULL DEFAULT '0' COMMENT '日期，零点的时间戳',
  login_time int(11) NOT NULL DEFAULT '0' COMMENT '该天总登录时间。',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY datestr (datestr),
  KEY dateint (dateint)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户登录时间日志'
html;
Db::query($sql);








echo "创建<br>\n";
