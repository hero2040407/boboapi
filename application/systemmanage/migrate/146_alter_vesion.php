<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_version_android
add size int not null default 0 comment 'apk文件大小'
html;
Db::query($sql);






echo "创建<br>\n";
