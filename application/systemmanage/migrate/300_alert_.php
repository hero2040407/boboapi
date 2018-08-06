<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter TABLE bb_record_invite_starmaker 
add media_duration int not null default 0 comment '媒体时长，单位秒'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_record_invite_starmaker
add media_url varchar(255) not null default '' comment '媒体播放地址，是url'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_record_invite_starmaker
add media_pic varchar(255) not null default '' comment '媒体封面图，适用于视频'
html;
Db::query($sql);





echo "创建<br>\n";
