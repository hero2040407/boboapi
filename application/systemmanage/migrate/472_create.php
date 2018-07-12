<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
CREATE TABLE bb_audition_card (
  id int(11) NOT NULL AUTO_INCREMENT,
  serial varchar(255) NOT NULL DEFAULT '' COMMENT '卡号',
  online_type tinyint not null default 1 comment '1线上，2线下',
  uid    int     not null default 0 comment '用户id',
  status tinyint not null default 1 comment '1未使用，2可以使用，3已注销',
  create_time int not null default 0 comment '创建时间',
  type varchar(255) not null default '' comment '分类，以英文字母单词做分类',
  active_time  int  not null default 0 comment '激活时间',
  destroy_time  int not null default 0 comment '注销时间，就是使用后导致无效的时间',
  money_fen  int  not null default 0 comment '价格，单位分',
  has_pay tinyint not null default 0 comment '0未支付，1已支付',
  PRIMARY KEY (id),
  KEY uid (uid),
  unique serial (serial),
  index status(status),
  index type(type)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='试镜卡表'

 
html;
Db::query($sql);













echo "创建<br>\n";

