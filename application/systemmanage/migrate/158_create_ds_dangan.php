<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_dangan (
  id int(11) NOT NULL AUTO_INCREMENT,
  ds_id     int  not null default 0 comment '大赛id',
  uid       int  not null default 0 comment '用户id',
  caiyi     varchar(255) not null default '' comment '逗号分隔的才艺id，如2,3',      
  
  shengao   varchar(255) not null default '' comment '身高',
  zhuzhi    varchar(255) not null default '' comment '住址',
  youbian   varchar(255) not null default '' comment '邮编',
  xuexiao  varchar(255) not null default '' comment '就读学校',
  nianji    varchar(255) not null default '' comment '年级',
  zhengshu  varchar(255) not null default '' comment '等级证书',      
  create_time  int NOT NULL DEFAULT 0 COMMENT '创建时间',
  info    varchar(500) not null default ''   comment '其他说明',
  PRIMARY KEY (id),
  index uid(uid),
  index ds_id(ds_id)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛个人活动档案表'
html;
Db::query($sql);


echo "创建<br>\n";
