<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_lunbo
add pic_bignew varchar(255) not null default '' comment '新版大图片网址,201803新加' 
html;
Db::query($sql);


$sql=<<<html
alter TABLE ds_race
add banner_bignew varchar(255) not null default '' comment '新版banner网址,201803新加' 
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_task_activity
add bigpic_list_bignew varchar(1024) not null default '' comment '新版轮播图片数组 json数组格式,201803新加'
html;
Db::query($sql);




















echo "创建<br>\n";

