<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
CREATE TABLE bb_users_invite_register (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int not null default 0 comment '邀请人uid',
  phone int not null default 0 comment '新注册用户的手机号',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (id),
  index uid(uid),
  unique phone(phone)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户邀请注册表'
html;
Db::query($sql);




echo "创建<br>\n";
