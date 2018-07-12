<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
CREATE TABLE bb_advise_join (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  advise_id int not null default 0 comment '通告，对应bb_advise 表的主键id',  
  uid int not null default 0 comment '用户id',
  audition_cart_id int NOT NULL DEFAULT 0 COMMENT '试镜卡id',
  create_time int not null default 0 comment '报名时间',
  audition_time int not null default 0 comment '试镜时间',
  role_id int not null default 0 comment '角色id，对应  bb_advise_role 表的主键id', 
  status tinyint not null default 0 comment '1报名成功，2已试镜，3试镜结束',
  PRIMARY KEY (id),
 index advise_id(advise_id),
  index uid (uid),
  index audition_cart_id (audition_cart_id),
  index role_id (role_id),
  index status (status)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='通告用户参加表'


html;
Db::query($sql);






echo "创建<br>\n";

