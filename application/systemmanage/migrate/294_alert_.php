<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_rewind_like
add  index uid(uid)
html;
Db::query($sql);


$sql=<<<html
alter table bb_rewind_like
add  index room_id(room_id)
html;
Db::query($sql);

$sql=<<<html
alter table bb_rewind_like
change time time int   NOT NULL DEFAULT 0 COMMENT '创建时间'
html;
Db::query($sql);

$sql=<<<html
alter table bb_rewind_like
add  index time(time)
html;
Db::query($sql);

$sql=<<<html
alter table bb_rewind_like
add  count int  NOT NULL DEFAULT 1 COMMENT '增加的赞数'
html;
Db::query($sql);





echo "创建<br>\n";
