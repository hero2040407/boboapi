<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_tongji_log
add datestr varchar(255) not null default '' comment '类似20160101'
";
Db::query($sql);





echo "创建<br>\n";
