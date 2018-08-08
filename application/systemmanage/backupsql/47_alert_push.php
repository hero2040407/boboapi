<?php

/**
 * 修改bb_push表
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter table bb_push
add index room_id(room_id)
html;
Db::query($sql);


$sql=<<<html
alter table bb_record
add index room_id(room_id)
html;
Db::query($sql);


$sql=<<<html
alter table bb_rewind
add index room_id(room_id)
html;
Db::query($sql);



echo "修改bb_push表<br>\n";
