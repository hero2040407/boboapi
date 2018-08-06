<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql ="drop table bb_resouce_group";
Db::query($sql);
$sql ="drop table bb_resouce";
Db::query($sql);


$sql="
CREATE TABLE bb_resource_group (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  title  varchar(255) not null default '' comment '组名称',
  sort int not null default 0 comment '组排序',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '生成时间',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='资源组'
";
Db::query($sql);
$sql="
CREATE TABLE bb_resource (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  group_id int(11) NOT NULL DEFAULT '0' COMMENT '组ID',
  title varchar(255) not null default '' comment '资源名称',
  url varchar(255) not null default '' comment '下载地址',
  pic varchar(255) not null default '' comment '标志图片',
  type tinyint not null default 1 comment '1动图，2音乐',
  sort int not null default 0 comment '排序',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '生成时间',
  PRIMARY KEY (id),
  index group_id(group_id),
  index type(type)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
";
Db::query($sql);

echo "创建<br>\n";
