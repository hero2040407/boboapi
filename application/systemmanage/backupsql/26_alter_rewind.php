<?php

/**
 * 给回播表加价格字段
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_rewind
add price int not null default 0 comment '波币购买价' 
html;
Db::query($sql);




echo "给回播表加价格字段<br>\n";

