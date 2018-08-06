<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_record_like
change uid uid int not null default 0 comment '用户id'
";
Db::query($sql);

$sql="alter table bb_record_like
change room_id  room_id  varchar(255) not null default '' comment '房间id'
";
Db::query($sql);

$sql = "alter table bb_record_like
add index ip(ip)
";
Db::query($sql);

$sql = "alter table bb_record_like
add index uid(uid)
";
Db::query($sql);

$sql = "alter table bb_record_like
add index room_id(room_id)
";
Db::query($sql);





echo "创建<br>\n";
