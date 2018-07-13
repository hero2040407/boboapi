<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table bb_record
add time_length_second int not null default 0 comment '短视频时长，单位秒'

html;
Db::query($sql);














echo "创建<br>\n";

