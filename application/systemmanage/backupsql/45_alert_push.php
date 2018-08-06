<?php

/**
 * 修改bb_push表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_push
add index stream_name(stream_name)
html;
Db::query($sql);

$sql=<<<html
alter table bb_rewind
add index stream_name(stream_name)
html;
Db::query($sql);



echo "修改bb_push表<br>\n";
