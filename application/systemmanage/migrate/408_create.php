<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_starmaker_application (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  admin_name varchar(255) NOT NULL DEFAULT '' COMMENT '审核管理员名称',
  admin_time int(11) NOT NULL DEFAULT '0' COMMENT '审核时间',
  lianxiren varchar(255) NOT NULL DEFAULT '' COMMENT '联系人',
  phone varchar(255) NOT NULL DEFAULT '' COMMENT '联系方式',
  jianjie varchar(1500) NOT NULL DEFAULT '' COMMENT '简介',
  status tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未审核，1审核成功，2审核失败',
  uid int(11) NOT NULL DEFAULT '0' COMMENT 'uid',
  PRIMARY KEY (id),
  KEY uid (uid),
  key status(status),
  key phone(phone)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='导师申请资料表'
html;
Db::query($sql);



echo "创建<br>\n";

