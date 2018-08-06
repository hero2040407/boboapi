<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_money_prepare 
add money decimal(10,2) not null default 0 comment '报名费，单位元'
html;
Db::query($sql);









echo "创建<br>\n";
