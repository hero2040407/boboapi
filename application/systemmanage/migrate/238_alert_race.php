<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_tongji_log_today
add index data2(data2)    
html;
Db::query($sql);







echo "创建<br>\n";
