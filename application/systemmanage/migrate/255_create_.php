<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_user_weixin_id (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '消息接收者',
  gz_openid varchar(255) NOT NULL DEFAULT '' COMMENT '用户针对公众平台的openid',
  kf_openid varchar(255) NOT NULL DEFAULT '' COMMENT '用户针对开放平台的openid',
  unionid  varchar(255) NOT NULL DEFAULT '' COMMENT '用户的公共微信id',       
  create_time int not null default 0 comment '创建时间',
  update_time int not null default 0 comment '修改时间',
  is_active tinyint not null default 0 comment '1有效，0无效',      
  PRIMARY KEY (id),
  index gz_openid (gz_openid),
  index kf_openid (kf_openid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户微信号表'   
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_user_weixin_id 
add unique uid(uid)
html;

$sql=<<<html
alter TABLE bb_user_weixin_id
add unique unionid(unionid)
html;









echo "创建<br>\n";
