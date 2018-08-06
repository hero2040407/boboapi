<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_money_log 
change money money decimal(10,2)  not null default 0 comment '人民币费用，单位元' 
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race
change money money decimal(10,2)  not null default 0 comment '人民币费用，单位元'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_register_log
change money money decimal(10,2)  not null default 0 comment '人民币费用，单位元'
html;
Db::query($sql);







echo "创建<br>\n";
