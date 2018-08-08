<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter TABLE bb_users_invite_register 
add target_uid int not null default 0 comment '被邀请的，新注册的uid'
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_users_invite_register
add index target_uid(target_uid)
html;
Db::query($sql);





echo "创建<br>\n";
