<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE ds_money_prepare (
  id    int(11) unsigned NOT NULL AUTO_INCREMENT,
  uid   int(11) not null default 0 comment 'uid',
  phone varchar(255) not null default '' comment '手机号',
  order_no varchar(255) not null DEFAULT '' COMMENT '我们自己的订单号',
  ds_id int(11) not null DEFAULT '0' COMMENT '大赛id',
  create_time int not null  DEFAULT 0 COMMENT '订单下单时间',
  has_success tinyint not null DEFAULT '0'  COMMENT '0未验证 1 为成功 2为失败订单',
  terminal_type tinyint(4) NOT NULL DEFAULT '1' COMMENT '终端类型，1ios，2安卓',
  third_name   varchar(255) NOT NULL DEFAULT '' COMMENT '第三方支付名称',
  third_serial varchar(255) NOT NULL DEFAULT '' COMMENT '第三方支付订单号，反查第三方用',
  PRIMARY KEY (id),
  index uid(uid),
  index phone(phone),
  index order_no(order_no)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
html;
Db::query($sql);


echo "创建<br>\n";
