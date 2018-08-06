<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_register_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  ds_id       int          not null default 0 comment '大赛id',
  uid         int NOT NULL DEFAULT 0 COMMENT '报名者uid',
  create_time int not null default 0 comment '报名时间',
  has_join    tinyint not null default 0 comment '是否参加过，即上传过视频',
  money   int not null default 0 comment '交纳的报名费',  
  PRIMARY KEY (id),
  index uid(uid),
  index ds_id(ds_id)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛报名日志表'
html;
Db::query($sql);


echo "创建<br>\n";
