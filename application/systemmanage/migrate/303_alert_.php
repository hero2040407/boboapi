<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_users_signin_log
add bonus int not null default 0 comment '奖励波币'
html;
Db::query($sql);





echo "创建<br>\n";
