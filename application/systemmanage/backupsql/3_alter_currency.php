<?php

/**
 * bb_currency表
 * gold字段
 * null改成not null ，这样，程序中无需强转类型。
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_currency
change gold gold int not null default 0 comment '波币数量'        
";

Db::query($sql);

//
echo "修改了bb_currency<br>\n";

