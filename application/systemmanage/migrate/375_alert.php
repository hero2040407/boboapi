<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_money_rain_log 
drop column play_id_arr
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_money_rain_log
drop column result_str
html;
Db::query($sql);




echo "创建<br>\n";

