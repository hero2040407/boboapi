<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_pic (
  id int(11) unsigned  NOT NULL AUTO_INCREMENT,
  url varchar(255)   NOT NULL DEFAULT '' COMMENT '图片网址',
  type tinyint         not null default 0 comment '1大赛',
  height int           not null default 0 comment '图片高',
  width int           not null default 0 comment '图片宽',
  uid   int            not null default 0 comment 'uid',
  act_id           int NOT NULL DEFAULT 0 COMMENT 'type=1表示大赛id ',
  create_time int      NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (id),
  index type(type),
  index uid(uid),
  index act_id(act_id)
) ENGINE=innodb  DEFAULT CHARSET=utf8 comment="公共图片表"

html;
Db::query($sql);


echo "创建<br>\n";

