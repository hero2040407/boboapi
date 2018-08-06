<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
drop TABLE ds_caiyi
html;
Db::query($sql);

$sql=<<<html
CREATE TABLE ds_dangan (
  id int(11) NOT NULL AUTO_INCREMENT,
  ds_id     int  not null default 0 comment '大赛id',
  uid       int  not null default 0 comment 'uid',      
  config_id  int not null default 0 comment '配置id,对应ds_dangan_config表主键',      
  value    varchar(255) not null default '' comment '用户填写内容，复选框填1，文本框填内容，上传填文件web路径',      
  create_time  int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (id),
  index ds_id(ds_id),
  index uid(uid)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛个人档案表'
html;
Db::query($sql);


echo "创建<br>\n";
