<?php

/**
 * 创建地区表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_area (
  id int NOT NULL AUTO_INCREMENT,
  postcode varchar(255) not null default '' comment '邮政编码，如210000',
  name varchar(255) NOT NULL DEFAULT '' comment '地区名称，如南京市',
  level tinyint NOT NULL DEFAULT 0 comment '1省，2市，3区，4街道',
  parent int NOT NULL DEFAULT 0 comment '父id',
  path varchar(255) not null DEFAULT '' comment 'id路径，例如10,162，含当前id',
  wordpath varchar(255) not null default '' comment '文字路径，例如江苏省,南京市,玄武区',
  amap_code varchar(255) not null default '' comment '高德地图编码',
  shortpy varchar(255) not null default '' comment '例如xwq代表玄武区，每汉字只取拼音头字母',
  fullpy varchar(255) not null default '' comment '例如xuanwuqu代表玄武区',
  PRIMARY KEY (id),
  index postcode(postcode),
  index parent(parent),
  index path(path),
  index wordpath(wordpath)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8  COMMENT='地区表'
html;
Db::query($sql);


echo "创建地区表<br>\n";

