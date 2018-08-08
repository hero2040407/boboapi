<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_tongji_huizong
add push_time_today int  not null default 0 comment '当日看直播时长总计'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add push_time_all int not null default 0 comment '所有看直播时长总计'
";
Db::query($sql);




echo "创建<br>\n";
