<?php

/**
 * bb_currency表
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_currency
add lahei_count int not null default 0 comment '拉黑总人数'        
";

Db::query($sql);

$sql="alter table bb_currency
add index uid(uid)
";

Db::query($sql);


//
echo "修改bb_currency表<br>\n";

