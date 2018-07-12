<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_users_card_template (
  id     int NOT NULL AUTO_INCREMENT,
  title varchar(255) not null  default '' comment '模板标题',
  is_show tinyint not null default 1 comment '1正常，0隐藏',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='模卡模板表'
html;
Db::query($sql);



echo "创建<br>\n";

