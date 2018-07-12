<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
CREATE TABLE bb_question_official (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(500) NOT NULL DEFAULT '' COMMENT '问题',
  answer text NOT NULL  COMMENT '回答',
  sort int not null default 0 comment '大于0则为热门，且按从大到小排序',
  create_time int not null default 0 comment '创建时间',
  type tinyint not null default 0 comment '1充值问题，2直播问题，3账号问题，4封号查询，5其他问题', 
  PRIMARY KEY (id),
  KEY type (type),
  index sort(sort)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='官方问题表'

 
html;
Db::query($sql);













echo "创建<br>\n";

