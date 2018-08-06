<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_task_activity
  add index task_id(task_id)
";
Db::query($sql);






//
echo "修改bbcy表<br>\n";

