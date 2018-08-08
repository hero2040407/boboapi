<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter TABLE ds_race 
add is_active tinyint  not null default 1 comment '1有效，0无效'
html;
Db::query($sql);





echo "创建<br>\n";
