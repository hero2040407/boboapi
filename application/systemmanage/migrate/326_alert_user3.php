<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_users_starmaker 
add level  int not null default 1 comment '星推官等级'        
html;
Db::query($sql);









echo "创建<br>\n";
