<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_activity_comments
add index uid(uid)
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_activity_comments
add index activity_id(activity_id)
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_record_comments
add index uid(uid)
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_record_comments
add index activity_id(activity_id)
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_rewind_comments
add index uid(uid)
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_rewind_comments
add index activity_id(activity_id)
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_task_comments
add index uid(uid)
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_task_comments
add index activity_id(activity_id)
html;
Db::query($sql);












echo "创建<br>\n";

