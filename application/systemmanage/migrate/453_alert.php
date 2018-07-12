<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_offline_register_log
add race_status tinyint not null default 0 comment '11线下签到，12晋级，13淘汰。'
html;
Db::query($sql);


$sql=<<<html
alter TABLE ds_offline_register_log
add rank int not null default 0 comment '排名，从1开始，为0表示无名次'
html;
Db::query($sql);


$sql=<<<html
alter TABLE ds_offline_register_log
add index race_status(race_status)
html;
Db::query($sql);







echo "创建<br>\n";

