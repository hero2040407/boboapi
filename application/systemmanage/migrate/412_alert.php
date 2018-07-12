<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_users_starmaker
add phone varchar(255) not null default '' comment '导师手机，客服联系用' 
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_users_starmaker
add index phone(phone)
html;
Db::query($sql);




















echo "创建<br>\n";

