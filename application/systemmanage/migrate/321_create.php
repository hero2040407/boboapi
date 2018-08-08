<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_dashang_prepare (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  target_uid int(11) NOT NULL DEFAULT '0' COMMENT '被打赏人uid',
  phone varchar(255) NOT NULL DEFAULT '' COMMENT '打赏人手机号',
  order_no varchar(255) NOT NULL DEFAULT '' COMMENT '我们自己的订单号',
  room_id int(11) NOT NULL DEFAULT '0' COMMENT '视频id',
  present_id int(11) NOT NULL DEFAULT '0' COMMENT '礼物id',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '订单下单时间',
  has_success tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未验证 1 为成功 2为失败订单',
  terminal_type tinyint(4) NOT NULL DEFAULT '1' COMMENT '终端类型，1ios，2安卓',
  third_name varchar(255) NOT NULL DEFAULT '' COMMENT '第三方支付名称',
  third_serial varchar(255) NOT NULL DEFAULT '' COMMENT '第三方支付订单号，反查第三方用',
  openid varchar(255) NOT NULL DEFAULT '' COMMENT '对应服务号的openid',
  money_fen int NOT NULL DEFAULT 0 COMMENT '打赏费，单位分',
  PRIMARY KEY (id),
  KEY target_uid (target_uid),
  KEY phone (phone),
  KEY order_no (order_no),
  KEY third_serial (third_serial),
  KEY openid (openid),
  KEY room_id (room_id),
  KEY present_id (present_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
html;
Db::query($sql);



echo "创建<br>\n";
