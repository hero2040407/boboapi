<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_race_message (
  id       int NOT NULL AUTO_INCREMENT,
  ds_id    int NOT NULL DEFAULT '0' COMMENT '大赛id',
  field_id  int NOT NULL DEFAULT '0' COMMENT '大赛id',
  
  create_time   int not null default 0 comment '下单时间',
  pic varchar(255) not null  default '' comment '素材图片',
  order_id int not null default 0 comment '订单id，对应bb_users_card表的主键id',
  PRIMARY KEY (id),
  index uid (uid),
  index order_id (order_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='模卡用户素材表'
html;
Db::query($sql);



echo "创建<br>\n";

