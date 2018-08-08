<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_tongji_huizong
change movie_view_avg_current movie_view_avg_today float not null default 0 comment '当日看短视频平均'
";
Db::query($sql);






echo "创建<br>\n";
