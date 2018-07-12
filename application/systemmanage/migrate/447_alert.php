<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_request 
add post varchar(2000) not null default '' comment 'post的json数组'
html;
Db::query($sql);




echo "创建<br>\n";

