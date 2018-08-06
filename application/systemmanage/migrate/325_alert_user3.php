<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_users_starmaker 
add info  varchar(1000) not null default '' comment '星推官简介'        
html;
Db::query($sql);









echo "创建<br>\n";
