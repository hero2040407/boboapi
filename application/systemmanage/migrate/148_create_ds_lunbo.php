<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_lunbo (
  id int(11) NOT NULL AUTO_INCREMENT,
  ds_id   int          not null default 0 comment '大赛id',
  title   varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  pic     varchar(255) not null default '' comment '图片网址',
  url     varchar(255)      not null default 1  comment '点击图片后进入的网址',
  create_time int      not null default 0  comment '创建时间', 
  sort    int          not null default 0 comment '排序，数字大靠前',
  PRIMARY KEY (id),
  index ds_id(ds_id)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛轮播图表'
html;
Db::query($sql);


echo "创建<br>\n";
