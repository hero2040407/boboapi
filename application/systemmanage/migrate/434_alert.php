<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_dashang_log
add  agent  varchar(255) not null default '' comment '手机型号'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_dashang_log
add  ip  varchar(255) not null default '' comment 'ip'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_dashang_log
add index ip(ip)
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_dashang_log
add index agent(agent)
html;
Db::query($sql);









echo "创建<br>\n";

