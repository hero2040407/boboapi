<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter TABLE ds_race 
add scope_level tinyint  not null default 1 comment '1不限制报名，2只能后台报名'
html;
Db::query($sql);


echo "创建<br>\n";
