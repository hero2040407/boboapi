<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_ranking (
  id int NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid     int     not null default 0 comment '用户id',
  ranking int     not null default 0 comment '排名，从1开始',
  type    tinyint not null default 1 comment '1财富，2粉丝，3，等级经验，4，怪兽数量',
  PRIMARY KEY (id),
  index uid(uid),
  index ranking(ranking)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 comment "用户排名表"       
html;
Db::query($sql);


echo "创建<br>\n";
