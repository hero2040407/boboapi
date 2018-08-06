<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql="
alter table bb_resource_group
add type tinyint  not null default 1 comment '组类型'
";
Db::query($sql);

echo "创建<br>\n";
