<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_currency
add gold_bean int not null default 0 comment '波豆'
";
Db::query($sql);










echo "创建<br>\n";
