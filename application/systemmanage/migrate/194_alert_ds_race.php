<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race 
add is_app tinyint not null default 0 comment '0普通赛区，1app赛区'
html;
Db::query($sql);



echo "创建<br>\n";
