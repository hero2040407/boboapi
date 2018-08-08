<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_client_log
add orginal_name varchar(255) not null default '' comment '原始文件名'
html;
Db::query($sql);



echo "创建<br>\n";
