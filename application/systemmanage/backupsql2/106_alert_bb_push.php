<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_push
add create_time int not null default 0 comment '后加，php接口推流开始时间，不受趣拍影响'
";
Db::query($sql);










echo "创建<br>\n";
