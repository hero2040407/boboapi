<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_record
add  hot_days int not null default 0 comment '热门持续天数'        
html;
Db::query($sql);




echo "创建<br>\n";
