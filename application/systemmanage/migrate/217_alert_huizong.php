<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_tongji_huizong
add zan_count int  not null default 0 comment '点赞数'    
html;
Db::query($sql);







echo "创建<br>\n";
