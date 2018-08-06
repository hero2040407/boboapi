<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_record_comments_reply (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  comments_id int(11) NOT NULL COMMENT '活动ID',
  content varchar(1024) NOT NULL COMMENT '内容',
  time varchar(11) NOT NULL,
  uid int(11) NOT NULL,
  reply_count int(11) DEFAULT '0' COMMENT '回复数量',
  audit int(2) DEFAULT '0' COMMENT '是否认证 0：未认证 1：认证 2：失败',
  is_remove int(1) DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (id),
  KEY comments_id (comments_id),
  KEY uid (uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8     
html;
Db::query($sql);


echo "创建<br>\n";
