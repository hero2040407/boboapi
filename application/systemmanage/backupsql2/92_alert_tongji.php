<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_tongji_huizong
add register_count int not null default 0 comment '当日注册数'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add login1_count int not null default 0 comment '次日登录数'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add login2_count int not null default 0 comment '3日登陆数'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add login3_count int not null default 0 comment '7日登陆数'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add login4_count int not null default 0 comment '7日2次登录数'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add online_time int not null default 0 comment '平均在线时长'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add movie_view_count_today int not null default 0 comment '视频浏览数当日'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add movie_view_count_all int not null default 0 comment '视频浏览总数'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add movie_view_avg_current float not null default 0 comment '视频浏览数当日平均'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add movie_view_avg_all float not null default 0 comment '视频浏览数总平均'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add push_view_count_today int not null default 0 comment '看直播次数当日'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add push_view_count_all int not null default 0 comment '看直播次数总'
";
Db::query($sql);


$sql="alter table bb_tongji_huizong
add push_view_avg_today int not null default 0 comment '看直播次数当日平均'
";
Db::query($sql);

$sql="alter table bb_tongji_huizong
add push_view_avg_all int not null default 0 comment '看直播次数总总平均'
";
Db::query($sql);





echo "创建<br>\n";
