<?php

/**
 * 给直播表和录播表加价格字段
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_push
add price int not null default 0 comment '波币购买价' 
html;
Db::query($sql);

$sql=<<<html
alter table bb_record
add price int not null default 0 comment '波币购买价'
html;
Db::query($sql);


echo "给直播表和录播表加价格字段<br>\n";

