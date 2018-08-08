<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE lt_user_task
add has_complete tinyint  not null default 0 comment '1已完成，0未完成。'    
html;
Db::query($sql);

$sql=<<<html
alter TABLE lt_bonus
add current_count int  not null default 0 comment '奖品当前剩余数量'
html;
Db::query($sql);

$sql=<<<html
alter TABLE lt_bonus
add all_count int  not null default 0 comment '奖品开始的总数'
html;
Db::query($sql);





echo "创建<br>\n";
