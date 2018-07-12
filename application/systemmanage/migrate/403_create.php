<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_users_card_template_material (
  id     int NOT NULL AUTO_INCREMENT,
  template_id int not null  default 0 comment '模板id，对应bb_users_card_template的主键id',
  pic varchar(255) not null default '' comment '样例图片，1个模板有多个样例图片',
  PRIMARY KEY (id),
  index template_id(template_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='模卡模板样例表'
html;
Db::query($sql);



echo "创建<br>\n";

