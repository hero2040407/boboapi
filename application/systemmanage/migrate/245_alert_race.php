<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE lt_draw_log
add bonus_name varchar(255) not null default '' comment '奖品名称'  
html;
Db::query($sql);






echo "创建<br>\n";
