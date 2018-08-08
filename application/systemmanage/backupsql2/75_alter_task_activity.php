<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_user_activity
add record_id int not null default 0 comment '短视频id'
";
Db::query($sql);

$sql="alter table bb_user_activity
add room_id varchar(255) not null default 0 comment '房间id'
";
Db::query($sql);


$sql="alter table bb_user_activity
add like_count int not null default 0 comment '赞数'
";
Db::query($sql);

$sql="alter table bb_user_activity
add index record_id(record_id)
";
Db::query($sql);

$sql="alter table bb_user_activity
add index room_id(room_id)
";
Db::query($sql);




//
echo "修改bbcy表<br>\n";

