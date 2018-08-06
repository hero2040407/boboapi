<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
CREATE TABLE bb_vip_application_log (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL DEFAULT '0' COMMENT 'uid',
  money int          not null default 0 comment '缴纳费用，单位元，此字段只适用于status=1',
  name varchar(255) NOT NULL DEFAULT '' COMMENT '真实姓名，此字段只适用于status=2',
  phone varchar(255) NOT NULL DEFAULT '' COMMENT '联系方式，此字段只适用于status=2',
  jianjie varchar(1500) NOT NULL DEFAULT '' COMMENT '个人自我介绍，此字段只适用于status=2',
  admin_name varchar(255) NOT NULL DEFAULT '' COMMENT '审核管理员名称，此字段只适用于status=4',
  admin_time int(11) NOT NULL DEFAULT '0' COMMENT '审核时间，此字段只适用于status=4',
  status tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未审核，1认证费50元已成功缴纳，2已经填写申请资料，3已经完善了个人资料，4最终管理员审核通过',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (id),
  index uid (uid),
  index status(status),
  index phone(phone)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='vip申请日志表'
html;
Db::query($sql);



echo "创建<br>\n";

