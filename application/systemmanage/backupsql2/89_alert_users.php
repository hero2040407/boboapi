<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_users
add qudao varchar(255) not null default '' comment '渠道'
";
Db::query($sql);






echo "创建<br>\n";
