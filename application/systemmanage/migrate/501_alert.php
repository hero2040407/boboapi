<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table bb_users_updates
add agent_uid int not null default 0 comment '如果是经纪人发布的，设置经纪人uid'

html;
Db::query($sql);


$sql=<<<html
alter table bb_users_updates
add index agent_uid(agent_uid)

html;
Db::query($sql);











echo "创建<br>\n";

