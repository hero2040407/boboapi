<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_ad (
  id int(11) NOT NULL AUTO_INCREMENT,
  url varchar(255)  not null default '' comment '广告的网址',
  pic  varchar(255)  not null default '' comment '广告的图片',
  create_time  int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (id) 
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='广告表'
html;
Db::query($sql);


echo "创建<br>\n";
