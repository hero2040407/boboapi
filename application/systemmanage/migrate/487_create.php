<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
CREATE TABLE bb_advise_role (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  advise_id int not null default 0 comment '通告，对应bb_advise 表的主键id',  

  title varchar(255) NOT NULL DEFAULT '' COMMENT '角色名称',
  create_time int not null default 0 comment '创建时间',
  sex tinyint not null default 1 comment '1男，0女',
  min_age    int not null default 0 comment '最小年龄，',
  max_age    int not null default 0 comment '最大年龄，',
  min_height  int not null default 100 comment '最小身高',
  max_height  int not null default 160 comment '最大身高',
  reward varchar(255) not null default '面议' comment '报酬，文字描述',
  content  varchar(3000) not null default '' comment '角色详情',
  PRIMARY KEY (id),
 index advise_id(advise_id),
  index min_age (min_age),
  index max_age (max_age),
  index min_height (min_height),
  index max_height (max_height)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='通告角色表'



html;
Db::query($sql);






echo "创建<br>\n";

