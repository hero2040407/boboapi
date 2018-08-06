<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_dashang_log
add record_type tinyint NOT NULL DEFAULT '0' COMMENT '1直播, 2短视频'
html;
Db::query($sql);










echo "创建<br>\n";

