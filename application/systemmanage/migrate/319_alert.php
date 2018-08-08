<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter table lt_roulette
add  current_count int not null default 0 comment '剩余数量'
html;
Db::query($sql);



echo "创建<br>\n";
