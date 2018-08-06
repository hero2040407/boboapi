<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_request
add user_agent varchar(500) not null default '' comment '头信息user_agent'
";
Db::query($sql);










echo "创建<br>\n";
