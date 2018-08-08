<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_request
add datestr char(8) not null default '' comment '日期'
";
Db::query($sql);










echo "创建<br>\n";
