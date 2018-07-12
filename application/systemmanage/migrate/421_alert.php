<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_record
add  transcoding_complete  tinyint not null default 1 comment '转码完成是1，一般的常规视频默认为1' 
html;
Db::query($sql);




















echo "创建<br>\n";

