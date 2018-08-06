<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_money_rain_log 
drop index index1
html;
Db::query($sql);



echo "创建<br>\n";

