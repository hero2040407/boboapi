<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_users_starmaker 
add title  varchar(255) not null default '' comment '头衔，如儿童街舞导师'        
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_users_starmaker
add week  varchar(255) not null default '' comment '本周在线时间，周一是1，周日是7，英文逗号分隔的字符串,如1,3'
html;
Db::query($sql);










echo "创建<br>\n";
