<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_msg_user_config (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  bigtype tinyint NOT NULL DEFAULT 1 COMMENT '1普通的消息类型，2兴趣爱好类型',
  type int not null default 0 comment 'bigtype为1：119视频被赞，122被关注，123好友传视频，124好友开直播，bigtype为2：兴趣爱好的id',
  title varchar(255) NOT NULL DEFAULT '' COMMENT '类型标题',
  uid int not null default 0 comment '用户id',
  value tinyint not null default 1 comment '1接受推送，0不接受。',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY type (type)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='系统消息推送用户设置表'   
html;
Db::query($sql);




echo "创建<br>\n";
