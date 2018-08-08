<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_focus
change time time int not null default 0 comment '创建时间'        
";

Db::query($sql);





//
echo "修改bbcy表<br>\n";

