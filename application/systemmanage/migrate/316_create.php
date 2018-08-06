<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
CREATE TABLE bb_comment_public_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid        int NOT NULL DEFAULT 0 COMMENT '发起评论的用户，是机器人',
  target_uid int NOT NULL DEFAULT 0 COMMENT '目标用户',
  content  varchar(1000) not null default 0 comment '评论内容',
  has_comment tinyint not null default 0 comment '0未评论，1已评论',      
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='随机评论日志表'
html;
Db::query($sql);




echo "创建<br>\n";
