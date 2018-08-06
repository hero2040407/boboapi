<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race 
add has_dangan tinyint not null default 0 comment '1有档案，0无档案' 
html;
Db::query($sql);







echo "创建<br>\n";
