<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table ds_record
add  rank  int not null default 0 comment '在大赛中的名次，从1开始'
html;
Db::query($sql);

$sql=<<<html
alter table ds_record
add  index rank(rank)
html;
Db::query($sql);




echo "创建<br>\n";
