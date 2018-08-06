<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter TABLE bb_record_invite_starmaker 
add lock_time int not null default 0 comment '上锁时间，正在点评，半小时后失效'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_record_invite_starmaker
add index lock_time(lock_time)
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_record_invite_starmaker
add lock_uid int not null default 0 comment '上锁人uid，正在点评，半小时后失效'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_record_invite_starmaker
add index lock_uid(lock_uid)
html;
Db::query($sql);




echo "创建<br>\n";
