<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_caiyi (
  id int(11) NOT NULL AUTO_INCREMENT,
  title      varchar(255)        not null default '' comment '才艺标题，如唱歌',
  group_id  int NOT NULL DEFAULT 0 COMMENT '组id',
  sort   int not null default 0 comment '次序，大的靠前',
  create_time int not null default 0 comment '创建时间',
  PRIMARY KEY (id)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛才艺表'
html;
Db::query($sql);


echo "创建<br>\n";
