<?php

/**
 * 修改回播表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_rewind
add address varchar(255)  not null default '' comment '原来的直播地址'
html;
Db::query($sql);




echo "修改回播表<br>\n";

