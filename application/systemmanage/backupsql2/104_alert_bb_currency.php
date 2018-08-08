<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_currency
add login2 tinyint not null default 0 comment '连续2日登陆'
";
Db::query($sql);

$sql="
alter table bb_currency
add login3 tinyint not null default 0 comment '连续3日登陆'
";
Db::query($sql);

$sql="
alter table bb_currency
add login7 tinyint not null default 0 comment '连续7日登陆'
";
Db::query($sql);









echo "创建<br>\n";
