<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_subject
add  subtitle  varchar(255)  not null default '' comment '副标题'
";
Db::query($sql);




echo "创建<br>\n";
