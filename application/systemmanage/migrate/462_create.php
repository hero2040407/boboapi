<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_user_log (
  id       int NOT NULL AUTO_INCREMENT,
  ds_id    int NOT NULL DEFAULT '0' COMMENT '大赛id',
  create_time   int not null default 0 comment '下单时间',
  uid      int not null default 0 comment '用户id',  
  type  int not null default 1 comment '类型，暂未使用',
  title varchar(255) not null default '' comment '动态的日志标题',
  content  varchar(255) not null default '' comment '动态日志内容',
  PRIMARY KEY (id),
  index ds_id(ds_id),
  index uid(uid)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='用户参数动态日志表'
html;
Db::query($sql);



echo "创建<br>\n";

