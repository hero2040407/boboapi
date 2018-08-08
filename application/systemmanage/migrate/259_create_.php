<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_baoming (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL COMMENT '用户ID',
  price decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单总金额',
  ds_id int(11) NOT NULL DEFAULT '0' COMMENT '大赛id',
  info varchar(255) NOT NULL DEFAULT '' COMMENT '系统消息内容',
  is_success tinyint(4) NOT NULL DEFAULT '0' COMMENT '0待定，1成功付款',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '生成时间',
  pay_time int(11) NOT NULL DEFAULT '0' COMMENT '用户付款时间',
  msg_id  int(11) NOT NULL DEFAULT '0' COMMENT '系统消息表主键id',
  PRIMARY KEY (id),
  index uid(uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 comment "用户报名付费表"  
html;
Db::query($sql);








echo "创建<br>\n";
