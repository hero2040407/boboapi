<?php

/**
 * 创建举报表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_config_jubao (
  id int NOT NULL AUTO_INCREMENT COMMENT '主键',
  content varchar(255) not null default '' comment '举报内容',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 comment "举报配置表"       
html;
Db::query($sql);


echo "创建<br>\n";
