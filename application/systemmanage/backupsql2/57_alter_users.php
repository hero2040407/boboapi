<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_users
change nickname nickname varchar(255)  not null default '' comment '用户昵称'        
";

Db::query($sql);





//
echo "修改bbcy表<br>\n";

