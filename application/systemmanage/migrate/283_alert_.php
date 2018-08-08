<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_record_like
change time time int not null default 0 comment '点赞时间'
html;
Db::query($sql);

$sql=<<<html
alter table bb_record_like
add index time(time)
html;
Db::query($sql);



echo "创建<br>\n";
