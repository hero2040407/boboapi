<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_dashang_log
add present_id int not null default 0 comment '礼物id'
";
Db::query($sql);


$sql="
alter table bb_dashang_log
add present_name varchar(255) not null default '' comment '礼物名称'
";
Db::query($sql);











echo "创建<br>\n";
