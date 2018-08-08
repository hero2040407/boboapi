<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
drop TABLE ds_dangan
html;
Db::query($sql);

$sql=<<<html
CREATE TABLE ds_dangan_config (
  id int(11) NOT NULL AUTO_INCREMENT,
  ds_id     int  not null default 0 comment '大赛id',
  title  varchar(255) not null default '' comment '配置名称',      
  type   tinyint not null default 0 comment '1复选框，2文本框，3上传，4简介',      
  sort int not null default 0 comment '排序，数字大靠前',
  create_time  int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (id),
  index ds_id(ds_id)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛档案配置表'
html;
Db::query($sql);


echo "创建<br>\n";
