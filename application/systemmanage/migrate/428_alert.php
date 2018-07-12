<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_label
add image2018 varchar(255) not null default '' comment '上传短视频时的才艺选择图片。'
html;
Db::query($sql);





















echo "创建<br>\n";

