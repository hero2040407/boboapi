<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE ds_money_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  ds_id     int  not null default 0 comment '大赛id',
  uid       int  not null default 0 comment 'uid',      
  money     decimal not null default 0 comment '一定正数，交了多少钱',      
  create_time  int NOT NULL DEFAULT 0 COMMENT '创建时间',
  PRIMARY KEY (id),
  index ds_id(ds_id),
  index uid(uid)
 ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛报名付费日志表'
html;
Db::query($sql);



echo "创建<br>\n";
