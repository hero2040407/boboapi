<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
use BBExtend\Sys;

$sql=<<<html
alter table bb_money_rain_log
add  pay int not null default 0 comment '玩游戏手续费，单位波币，负数表示'
html;
Db::query($sql);












echo "创建<br>\n";
