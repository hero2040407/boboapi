<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
CREATE TABLE bb_face (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  parent int(11) NOT NULL DEFAULT '0' COMMENT '组ID，为0表示是组图片，否则是普通的图片',
  pic varchar(255) NOT NULL DEFAULT '' COMMENT '标志图片',
  sort int(11) NOT NULL DEFAULT '0' COMMENT '排序,从大到小',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '生成时间',
  PRIMARY KEY (id),
  index parent(parent)
) ENGINE=innodb  DEFAULT CHARSET=utf8
html;
Db::query($sql);











echo "创建<br>\n";

