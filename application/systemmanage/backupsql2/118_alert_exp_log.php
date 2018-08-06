<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_users_exp_log
add create_time int not null default 0 comment '时间戳'
";
Db::query($sql);

$sql="
alter table bb_users_exp_log
add datestr char(8) not null default '' comment '时间戳的日期字符串，类似20160101'
";
Db::query($sql);

$sql="
alter table bb_users_exp_log
add index datestr(datestr)
";
Db::query($sql);












echo "创建<br>\n";
