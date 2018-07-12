<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_users_starmaker
add zhuanye varchar(1000) not null default '' comment '导师专业，最多8条，竖线分隔' 
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_users_starmaker
add huojiang varchar(1000) not null default '' comment '导师证书及获奖情况，最多8条，竖线分隔'
html;
Db::query($sql);



















echo "创建<br>\n";

