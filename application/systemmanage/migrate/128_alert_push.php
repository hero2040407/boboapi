<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql="
alter table bb_push
add index uid(uid)
";
Db::query($sql);


echo "创建<br>\n";

