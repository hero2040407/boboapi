<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE bb_users_starmaker
add income int not null default 0 
comment '点评短视频获得的收入，是波豆'
html;
Db::query($sql);



echo "创建<br>\n";
