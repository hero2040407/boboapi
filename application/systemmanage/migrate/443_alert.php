<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_race
add  index proxy_id( proxy_id )
html;
Db::query($sql);





echo "创建<br>\n";

