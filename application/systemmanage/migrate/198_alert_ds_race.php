<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_speciality 
add num int not null default 0 comment '排序'
html;
Db::query($sql);





echo "创建<br>\n";
