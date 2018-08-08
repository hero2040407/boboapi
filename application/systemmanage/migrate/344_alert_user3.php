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
add  result_str varchar(1000) not null default '' comment '结果统计的序列化。'
html;
Db::query($sql);












echo "创建<br>\n";
