<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter TABLE ds_race 
add reward varchar(1000)  not null default '' comment '大赛奖励'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race
add address varchar(255)  not null default '' comment '非常详细的地址，到多少路多少号'
html;
Db::query($sql);



echo "创建<br>\n";
