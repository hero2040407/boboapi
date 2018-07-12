<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
CREATE TABLE ds_race_field (
  id int(11)  NOT NULL AUTO_INCREMENT comment '主键，账号' ,
  race_id int not null default 0 comment '大赛id',
  address     varchar(255) not null default '' comment '详细地址',
  start_time  int not null default 0 comment '起始时间戳,一般取那天的零点',
  end_time    int not null default 0 comment '结束时间戳,一般取那天的23：59',
  create_time int not null default 0 comment '创建时间',
  channel_id  int not null default 0 comment '渠道商id，有唯一索引',
  is_valid    int not null default 0 comment '1有效，0无效',
  PRIMARY KEY (id),
  index race_id(race_id),
  unique channel_id(channel_id)
) ENGINE=innodb DEFAULT CHARSET=utf8 COMMENT='赛区表'

html;
Db::query($sql);




echo "创建<br>\n";

