<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_moive_view_stats
add index type(type)
html;
Db::query($sql);

$sql=<<<html
alter table bb_moive_view_stats
add index usersort(usersort)
html;
Db::query($sql);


echo "创建<br>\n";
