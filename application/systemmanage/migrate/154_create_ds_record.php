<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_record (
  id int(11) NOT NULL AUTO_INCREMENT,
  ds_id       int          not null default 0 comment '大赛id',
  uid         int NOT NULL DEFAULT 0 COMMENT '参赛者uid',
  record_id   int not null default 0 comment '短视频id',
  create_time int not null default 0 comment '报名时间',
  PRIMARY KEY (id),
  index uid(uid),
  index ds_id(ds_id),
  index record_id(record_id)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛短视频表'
html;
Db::query($sql);


echo "创建<br>\n";
