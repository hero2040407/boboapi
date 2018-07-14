<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table bb_users_updates
add like_count int not null default 0 comment '赞数'

html;
Db::query($sql);














echo "创建<br>\n";

