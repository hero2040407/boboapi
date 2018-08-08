<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
CREATE TABLE bb_shanghu (
  id int(11) NOT NULL AUTO_INCREMENT,
  qrcode varchar(255) NOT NULL DEFAULT '' COMMENT '二维码图片',
  name  varchar(255) NOT NULL DEFAULT '' COMMENT '商户名称',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商户表'
html;
Db::query($sql);




echo "创建<br>\n";
