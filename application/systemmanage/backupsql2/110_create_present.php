<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_present (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  title      varchar(255) not null default '' comment '礼物名',
  pic        varchar(255) NOT NULL DEFAULT '' COMMENT '图片',
  gold       int          NOT NULL DEFAULT 0  COMMENT '波币数',
  experience int          NOT NULL DEFAULT 0  COMMENT '加多少经验',
  create_time int         NOT NULL DEFAULT 0  COMMENT '创建时间',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='礼物表'
html;
Db::query($sql);


echo "创建<br>\n";
