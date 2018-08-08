<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
CREATE TABLE bb_users_signin_log (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  create_time int not null default 0 comment '签到时间',
  datestr int not null default 0 comment '类似20171017这样的日期表示，签到日期文字表示',
  order_number int not null default 1 comment '只有可能是1到7，不会其他。表示连续签到的第几天，从1开始，循环，中断也从1开始',
  played_count int not null default 0 comment '真实发生的，当天用户玩过的幸运转盘次数，默认0，玩一次记一次',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY datestr (datestr),
  KEY order_number (order_number),
  KEY create_time(create_time)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='每日签到表'
html;
Db::query($sql);




echo "创建<br>\n";
