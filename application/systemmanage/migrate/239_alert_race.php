<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE lt_roulette
add rate int not null default 10 comment '概率，整数'  
html;
Db::query($sql);







echo "创建<br>\n";
