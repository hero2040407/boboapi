<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_tongji_log
add index datestr(datestr)
";
Db::query($sql);

$sql="alter table bb_tongji_log
add index info(info)
";
Db::query($sql);






echo "创建<br>\n";
