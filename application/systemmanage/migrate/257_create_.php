<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_baoming_order_prepare (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  price decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总价',
  ds_id int(11) NOT NULL DEFAULT '0' COMMENT '一件商品id',
  info varchar(255) NOT NULL DEFAULT '' COMMENT '订单介绍',
  type tinyint(4) NOT NULL DEFAULT '0' COMMENT '1现金，2bo币',
  is_success tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未付款，1已付款且复制到正式订单表',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  update_time int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  third_name varchar(255) NOT NULL DEFAULT '' COMMENT '第三方支付名称',
  third_serial varchar(255) NOT NULL DEFAULT '' COMMENT '第三方支付订单号，反查第三方用',
  PRIMARY KEY (id),
  KEY uid (uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛报名预生成订单表'   
html;
Db::query($sql);








echo "创建<br>\n";
