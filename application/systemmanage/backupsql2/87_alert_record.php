<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_record
add fail_reason varchar(255) not null default '' comment '审核失败原因'
";
Db::query($sql);






echo "创建<br>\n";
