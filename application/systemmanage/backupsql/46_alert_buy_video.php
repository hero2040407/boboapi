<?php

/**
 * 修改bb_buy_video表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_buy_video
add room_id varchar(255) not null default '' comment '视频房间id'
html;
Db::query($sql);

$sql=<<<html
alter table bb_buy_video
add index room_id(room_id)
html;
Db::query($sql);



echo "修改bb_buy_video表<br>\n";
