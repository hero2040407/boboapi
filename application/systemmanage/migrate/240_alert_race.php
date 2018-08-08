<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE lt_draw_log
add has_exchange tinyint not null default 0 comment '1已兑换，0未兑换'  
html;
Db::query($sql);







echo "创建<br>\n";
