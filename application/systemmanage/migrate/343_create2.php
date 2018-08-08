<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_money_rain_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL DEFAULT '0' COMMENT 'uid',
  random varchar(255) not null default '' comment '随机数，校验用',
  user_agent varchar(255) not null default '' comment '用户代理，结算检查用',  
  play_id_arr varchar(8000) not null default '' comment '点击按钮后的发送id，以及值。序列化过了',   
  datestr int not null default 0 COMMENT 'play时间的天，例如20180104',
  create_time int not null DEFAULT 0 COMMENT '当时时间',
  balance_time int not null default 0 comment '结算时间',
  result_gold int not null default 0 comment '得到的波币总和，可能是负数',
  PRIMARY KEY (id),
  index uid(uid),
  index datestr(datestr),
  unique index1(uid, datestr)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='天降红包日志表'

html;
Db::query($sql);



echo "创建<br>\n";
