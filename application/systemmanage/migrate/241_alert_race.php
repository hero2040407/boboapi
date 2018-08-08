<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_expression_package
add is_limit tinyint not null default 0 comment '1是限定表情包，可抽奖，0普通表情包'  
html;
Db::query($sql);







echo "创建<br>\n";
