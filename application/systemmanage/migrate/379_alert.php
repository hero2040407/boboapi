<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_money_rain_reward 
add score int  not null default 0 comment '原先玩的当日最高积分'
html;
Db::query($sql);







echo "创建<br>\n";

