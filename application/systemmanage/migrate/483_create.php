<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;







$sql=<<<html
CREATE TABLE bb_users_agent (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int not null default 0 comment '经纪人bobo号，必须有',
  PRIMARY KEY (id),
 unique uid(uid)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='经纪人表'


html;
Db::query($sql);












echo "创建<br>\n";

