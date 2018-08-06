<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql="
CREATE TABLE bb_resouce_group (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  title  varchar(255) not null default '' comment '组名称',
  sort int not null default 0 comment '组排序',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '生成时间',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='资源组'
";
Db::query($sql);

echo "创建<br>\n";
