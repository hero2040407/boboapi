<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_task_activity
add gold_type tinyint not null default 1 comment '奖励类型，1波币，2波豆'
";
Db::query($sql);













echo "创建<br>\n";
