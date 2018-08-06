<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race 
add parent int not null default 0 comment '主赛场id，如果自身是主赛场，则为0' 
html;
Db::query($sql);







echo "创建<br>\n";
