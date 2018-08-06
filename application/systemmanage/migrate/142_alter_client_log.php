<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_client_log
add type varchar(255) not null default '' comment '文件类型'
html;
Db::query($sql);



echo "创建<br>\n";
