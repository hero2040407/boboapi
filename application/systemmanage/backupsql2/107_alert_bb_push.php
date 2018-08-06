<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_push
add end_time int not null default 0 comment '用于nodejs计算时间用'
";
Db::query($sql);










echo "创建<br>\n";
