<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
drop TABLE bb_bottom_bar_rule 
html;
Db::query($sql);


$sql=<<<html
drop TABLE bb_bottom_bar_pic
html;
Db::query($sql);


$sql=<<<html
CREATE TABLE bb_bottom_bar_rule (
  id int(11) unsigned NOT NULL AUTO_INCREMENT comment '主键,规则id，越大越优先' ,
  title  varchar(255) not null default '' comment '规则名称',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间，必须记录',
  start_time int(11) NOT NULL DEFAULT '0' COMMENT '规则：开始时间，为0表示没有限制', 
  end_time int(11) NOT NULL DEFAULT '0' COMMENT '规则：结束时间，为0表示没有限制',
  zip_path   varchar(255) not null default '' comment '这个值必须有，是zip文件的网址，必须有域名',
  version   int not null default 1 comment '后台需保证本表所有的version字段值保持一致，每修改任何一张图片，所有行的version都加1',
  PRIMARY KEY (id)
) ENGINE=innodb DEFAULT CHARSET=utf8 COMMENT='app底部导航规则表'
html;
Db::query($sql);

$sql=<<<html
CREATE TABLE bb_bottom_bar_pic (
  id int(11) unsigned NOT NULL AUTO_INCREMENT comment '主键' ,
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间，必须记录。',
  rule_id int(11) NOT NULL DEFAULT '0' COMMENT '规则id， 对应(app底部导航规则表)的主键id',
  pic_key varchar(255) not null default '' comment '图标的键，目前只能是show,friend,live,activity,my中的一个',
  color_2x_url   varchar(255) not null default '' comment '图片网址，彩色2x',
  color_3x_url   varchar(255) not null default '' comment '图片网址，彩色3x',
  gray_2x_url   varchar(255) not null default '' comment '图片网址，灰色2x',
  gray_3x_url   varchar(255) not null default '' comment '图片网址，灰色3x',
  index rule_id(rule_id),
  PRIMARY KEY (id)
) ENGINE=innodb DEFAULT CHARSET=utf8 COMMENT='app底部导航图片表'
html;
Db::query($sql);




echo "创建<br>\n";

