<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race 
add sort int not null default 0 comment '排序，大的靠前'
html;
Db::query($sql);




echo "创建<br>\n";
