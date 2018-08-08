<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_dashang_log
add room_id varchar(255)  not null default '' comment '房间id，所有视频唯一'        
";

Db::query($sql);


//
echo "修改bbcy表<br>\n";

