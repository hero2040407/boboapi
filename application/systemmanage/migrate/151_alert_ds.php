<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter TABLE ds_race 
add money int not null default 0 comment '报名费'
html;
Db::query($sql);


echo "创建<br>\n";
