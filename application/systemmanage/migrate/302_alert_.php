<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_dashang_log
add index uid(uid)
html;
Db::query($sql);





echo "创建<br>\n";
