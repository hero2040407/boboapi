<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_users_starmaker 
add index level(level)        
html;
Db::query($sql);



$sql=<<<html
alter TABLE bb_users_starmaker
add index week(week)
html;
Db::query($sql);









echo "创建<br>\n";
