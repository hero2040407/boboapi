<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table bb_users_info
add vip_time int not null default 0 comment '正式成为vip童星的时间，应该是后台管理员的审核时间'

html;
Db::query($sql);


$sql=<<<html
alter table bb_users_info
add index vip_time(vip_time)

html;
Db::query($sql);








echo "创建<br>\n";

