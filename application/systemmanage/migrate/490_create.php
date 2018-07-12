<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
CREATE TABLE bb_users_recommend (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int not null default 0 comment '用户id',
  create_time int NOT NULL DEFAULT 0 COMMENT '排序用字段，时间越新，显示越靠前',
  is_upgrade tinyint not null default 0 comment '1新晋童星，0推荐童星',
  PRIMARY KEY (id),
  index uid (uid),
  index create_time (create_time)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='主打童星设置表'

html;
Db::query($sql);






echo "创建<br>\n";

