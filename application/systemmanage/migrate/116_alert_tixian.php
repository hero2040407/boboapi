<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_tixian_apply
add index datestr(datestr)
";
Db::query($sql);


$sql="
alter table bb_tixian_log
add index datestr(datestr)
";
Db::query($sql);

$sql="
alter table bb_tixian_apply
add openid varchar(255) not null default '' comment '微信openid'
";
Db::query($sql);

$sql="
alter table bb_tixian_log
add openid varchar(255) not null default '' comment '微信openid'
";
Db::query($sql);















echo "创建<br>\n";
