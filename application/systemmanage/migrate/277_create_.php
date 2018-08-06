<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
CREATE TABLE bb_users_nickname_change (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid         int     not null default 0 comment '用户id',      
  old_nickname varchar(255) NOT NULL DEFAULT '' COMMENT '原有昵称',
  new_nickname varchar(255) NOT NULL DEFAULT '' COMMENT '新昵称',
  create_time int     not null default 0 comment '申请时间',
  status      tinyint not null default 0 comment '0待审核，1审核过，2审核不通过',
  PRIMARY KEY (id),
  index uid(uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 comment='昵称修改日志表'
html;
Db::query($sql);


echo "创建<br>\n";
