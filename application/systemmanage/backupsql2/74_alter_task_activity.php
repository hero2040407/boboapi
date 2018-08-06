<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_user_activity
add paiming int not null default 0 comment '排名，1最大'
";
Db::query($sql);







//
echo "修改bbcy表<br>\n";

