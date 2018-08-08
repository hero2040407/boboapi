<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_minganci (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255)  not null default '' comment '敏感词',
  type tinyint not null default 1 comment '暂未使用',
  create_time  int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (id),
  index name(name)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='敏感词表'
html;
Db::query($sql);


echo "创建<br>\n";
