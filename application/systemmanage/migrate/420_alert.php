<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_aliyun_record
add  target_path  varchar(255) not null default '' comment '转码后的视频路径' 
html;
Db::query($sql);




















echo "创建<br>\n";

