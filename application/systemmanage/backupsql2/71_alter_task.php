<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_task
change info info  varchar(3000) not null default '' comment '详情'
";
Db::query($sql);


$sql="alter table bb_task_activity
change info info  varchar(3000) not null default '' comment '详情'
";
Db::query($sql);



//
echo "修改bbcy表<br>\n";

