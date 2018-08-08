<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_baoming_order (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL COMMENT '用户ID',
  price decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单总金额',
  type tinyint(4) NOT NULL DEFAULT '0' COMMENT '1现金，2波币',
  ds_id int(11) NOT NULL DEFAULT '0' COMMENT '商品id',
  serial varchar(100) NOT NULL DEFAULT '' COMMENT '订单号',
  is_success tinyint(4) NOT NULL DEFAULT '0' COMMENT '0待定，1成功付款',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '生成时间',
  update_time int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  third_name varchar(255) NOT NULL DEFAULT '' COMMENT '第三方支付名称',
  third_serial varchar(255) NOT NULL DEFAULT '' COMMENT '第三方支付订单号，反查第三方用',
  PRIMARY KEY (id),
  index uid(uid)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8  
html;
Db::query($sql);








echo "创建<br>\n";
