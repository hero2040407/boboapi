<?php

/**
 * bb_version表
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_version
add jubao int not null default 1 comment '举报内容版本';       
";

Db::query($sql);




//
echo "修改bb_version表<br>\n";

