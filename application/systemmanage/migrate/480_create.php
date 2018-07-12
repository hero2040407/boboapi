<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
CREATE TABLE bb_audition_card_type (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  name varchar(255) NOT NULL DEFAULT '' COMMENT '类型名称',
  PRIMARY KEY (id)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='试镜卡类型表'

 
html;
Db::query($sql);













echo "创建<br>\n";

