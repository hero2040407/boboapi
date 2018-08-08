<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_tongji_log
add money decimal(10,2)  not null default 0 comment '金额统计'    
html;
Db::query($sql);







echo "创建<br>\n";
