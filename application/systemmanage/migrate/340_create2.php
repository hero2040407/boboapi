<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_oss (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  type tinyint  NOT NULL DEFAULT '1' comment '1uploads图片，2public图片，3视频' ,
  old_table varchar(255) NOT NULL DEFAULT '' comment '原来的表名',
  old_column varchar(255) NOT NULL DEFAULT '' comment '原来的列名',
  old_value varchar(500) NOT NULL DEFAULT '' comment '原来的列的值',
  new_value varchar(500) NOT NULL DEFAULT '' comment '新的列的值',
  new_backup_file_path varchar(255) NOT NULL DEFAULT '' comment '该文件在本机的备份位置完整路径',
  create_time int(11) NOT NULL DEFAULT '0' comment '创建时间',
  PRIMARY KEY (id),
  key old_value(old_value),
  key new_value(new_value)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8

html;
Db::query($sql);



echo "创建<br>\n";
