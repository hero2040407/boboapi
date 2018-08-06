<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_race (
  id int(11) NOT NULL AUTO_INCREMENT,
  title   varchar(255) NOT NULL DEFAULT '' COMMENT '大赛名称',
  area    varchar(255) not null default '' comment '地区名，即分赛场名称',
  level   tinyint      not null default 1  comment '1主赛场，2分赛场',
  banner  varchar(255) not null default '' comment 'banner',
  uid     int          not null default 0  comment '主办人id',
  summary varchar(255) NOT NULL DEFAULT '' COMMENT '简介',
  create_time int      not null default 0  comment '创建时间', 
  start_time int       not null default 0  comment '大赛开始时间',
  end_time    int      not null default 0  comment '大赛结束时间',
  register_start_time int not null default 0  comment '报名起始时间',
  register_end_time   int not null default 0  comment '报名结束时间',
        
  PRIMARY KEY (id),
 index uid(uid)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛表'
html;
Db::query($sql);


echo "创建<br>\n";
