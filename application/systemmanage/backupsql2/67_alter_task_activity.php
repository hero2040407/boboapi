<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_task_activity
drop column user_list
";
Db::query($sql);




//
echo "修改bbcy表<br>\n";

