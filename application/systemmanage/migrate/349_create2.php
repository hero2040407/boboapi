<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_brandshop_application (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  admin_name varchar(255) NOT NULL DEFAULT '' COMMENT '审核管理员名称',
  admin_time int NOT NULL DEFAULT '0' COMMENT '审核时间',
  jigou_name varchar(255) NOT NULL DEFAULT '' COMMENT '机构名称',
  lianxiren varchar(255) NOT NULL DEFAULT '' COMMENT '联系人',
  phone     varchar(255) NOT NULL DEFAULT '' COMMENT '联系方式',
  address   varchar(255) not null default '' comment '地址',
  jianjie   varchar(1500) not null default '' comment '机构简介',
  status    tinyint NOT NULL DEFAULT '0' COMMENT '0未审核，1审核成功，2审核失败',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 comment='品牌馆申请资料表'

html;
Db::query($sql);



echo "创建<br>\n";
