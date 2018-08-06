<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_users
change sex sex tinyint not null default 0 comment '性别：1男0女'
";
Db::query($sql);




//
echo "修改bbcy表<br>\n";

