<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_users
add live_cover varchar(255)  not null default '' comment '直播封面'    
html;
Db::query($sql);





echo "创建<br>\n";
