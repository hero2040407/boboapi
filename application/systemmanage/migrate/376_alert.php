<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_money_rain_log 
add score int  not null default 0 comment '获得的当日最高积分'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_money_rain_log
add unique index1(uid, datestr)
html;
Db::query($sql);




echo "创建<br>\n";

