<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_task
change min_age min_age int not null default 0 comment '0不限，否则是最小年龄限制'
";
Db::query($sql);

$sql="alter table bb_task
change max_age max_age int not null default 0 comment '0不限，否则是最大年龄限制'
";
Db::query($sql);

$sql="alter table bb_task
change level level int not null default 0 comment '0不限，否则是最低LV限制'
";
Db::query($sql);


$sql="alter table bb_task_activity
change min_age min_age int not null default 0 comment '0不限，否则是最小年龄限制'
";
Db::query($sql);

$sql="alter table bb_task_activity
change max_age max_age int not null default 0 comment '0不限，否则是最大年龄限制'
";
Db::query($sql);

$sql="alter table bb_task_activity
change level level int not null default 0 comment '0不限，否则是最低LV限制'
";
Db::query($sql);



//
echo "修改bbcy表<br>\n";

