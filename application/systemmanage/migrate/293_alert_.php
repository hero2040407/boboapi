<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_push_like
add  index uid(uid)
html;
Db::query($sql);


$sql=<<<html
alter table bb_push_like
add  index room_id(room_id)
html;
Db::query($sql);


$sql=<<<html
alter table bb_push_like
add  index time(time)
html;
Db::query($sql);






echo "创建<br>\n";
