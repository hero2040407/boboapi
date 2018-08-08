<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE bb_record_invite_starmaker
add comment_time int not null default 0
comment '导师点评时间'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_record_invite_starmaker
add check_time int not null default 0
comment '管理员审核点评的时间'
html;
Db::query($sql);




echo "创建<br>\n";
