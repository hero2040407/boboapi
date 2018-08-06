<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
CREATE TABLE bb_users_shanghu_invite_register (
  id int(11) NOT NULL AUTO_INCREMENT,
  shanghu_id int(11) NOT NULL DEFAULT '0' COMMENT '商户d',
  phone varchar(255) NOT NULL DEFAULT '' COMMENT '手机号',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  is_complete tinyint(4) NOT NULL DEFAULT '0' COMMENT '1,邀请实现，注册过，0未实现',
  target_uid int(11) NOT NULL DEFAULT '0' COMMENT '被邀请的，新注册的uid',
  PRIMARY KEY (id),
  UNIQUE KEY phone (phone),
  KEY shanghu_id (shanghu_id),
  KEY target_uid (target_uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商户邀请注册表'
html;
Db::query($sql);




echo "创建<br>\n";
