<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
CREATE TABLE bb_money_rain_reward (

  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  datestr int not null default 0 comment '发奖日期，类似20180201',
  create_time int not null default 0 comment '发奖当时的时间戳',
  sort int not null default 0 comment '名次，从1开始',
  uid int not null  default 0 comment 'uid',
  money int not null default 0 comment '波币数',

  PRIMARY KEY (id),
  index uid (uid),
  index datestr(datestr)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='天降红包奖励表'
html;
Db::query($sql);



echo "创建<br>\n";

