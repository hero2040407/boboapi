<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_user_activity
add has_checked tinyint not null default 0 comment '1审核通过，0未审核'
";
Db::query($sql);


echo "创建<br>\n";
