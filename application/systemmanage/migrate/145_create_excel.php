<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_tongji_excel (
  id int(11) NOT NULL AUTO_INCREMENT,
  datestr char(8) NOT NULL DEFAULT '' COMMENT '日期',
  filename varchar(255) NOT NULL DEFAULT '' COMMENT '完整文件名',
  PRIMARY KEY (id)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='统计excel文件日志表'
html;
Db::query($sql);






echo "创建<br>\n";
