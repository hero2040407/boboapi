<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_money_rain_log 
add today_count int  not null default 1 comment '当日玩的次数'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_money_rain_log
add last_time int  not null default 0 comment '最后一次玩的时间'
html;
Db::query($sql);





echo "创建<br>\n";

