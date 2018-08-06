<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_users_invite_register
add is_complete tinyint not null default 0 comment '1,邀请实现，注册过，0未实现'
html;
Db::query($sql);



echo "创建<br>\n";
