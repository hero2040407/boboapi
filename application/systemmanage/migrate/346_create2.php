<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_brandshop (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  title varchar(255) NOT NULL DEFAULT '' COMMENT '头衔，例如街舞导师',
  info varchar(1000) NOT NULL DEFAULT '' COMMENT '星推官简介，纯文字的，放在品牌馆详情页面。',
  level int(11) NOT NULL DEFAULT '1' COMMENT '品牌馆等级',
  lng  decimal(10,6) not null default '120.15' comment '经度',
  lat decimal(10,6) not null default '30.28' comment '纬度',
  address varchar(255) not null default '浙江杭州' comment '实际地址，到城市为止',
  is_show tinyint NOT NULL DEFAULT '1' COMMENT '1被展示，0被隐藏，正常是1',
  PRIMARY KEY (id),
  index uid(uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 comment='品牌馆'

html;
Db::query($sql);



echo "创建<br>\n";
