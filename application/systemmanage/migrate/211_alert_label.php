<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_tongji_huizong
add liucun2 decimal(10,3) not null default 0 comment '次日留存'    
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_tongji_huizong
add liucun3 decimal(10,3) not null default 0 comment '三日留存'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_tongji_huizong
add liucun7 decimal(10,3) not null default 0 comment '七日留存'
html;
Db::query($sql);





echo "创建<br>\n";
