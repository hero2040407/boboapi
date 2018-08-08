<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
CREATE TABLE bb_users_info (
  id int   NOT NULL AUTO_INCREMENT,
  uid    int NOT NULL DEFAULT '0' COMMENT 'uid',
  height int not null default 0 comment '身高，单位厘米',
  weight int not null default 0 comment '体重，单位公斤',
  gexing   varchar(1000) not null default '' comment '个性，例如 ： 个性1|个性2|个性3',
  jingyan  varchar(1000) not null default '' comment '参赛经验，例如 ：经验1|经验2|经验3',
  update_time int not null default 0 comment '最后更新时间',
  PRIMARY KEY (id),
  unique uid (uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户附加信息表'
html;
Db::query($sql);



echo "创建<br>\n";

