<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_client_log
add version int not null default 0 comment '版本号'
html;
Db::query($sql);

$sql=<<<html
alter table bb_client_log
add index version(version)
html;
Db::query($sql);




echo "创建<br>\n";
