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
  is_valid  tinyint not null default 0 comment '1审核过，0未审核',
  create_time   int not null default 0 comment '下单时间',
  target_type tinyint not null default 1 comment '1成功者，2失败者',
  admin_id   int not null default 0 comment '管理员id',
  check_time   int not null default 0 comment '审核时间',
  PRIMARY KEY (id),
  index ds_id(ds_id),
  index field_id(field_id),
  index admin_id(admin_id)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='大赛消息表'
html;
Db::query($sql);



echo "创建<br>\n";

