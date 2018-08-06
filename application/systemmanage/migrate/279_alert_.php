<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_currency
add  score  int not null default 0 comment '积分'
html;
Db::query($sql);




echo "创建<br>\n";
