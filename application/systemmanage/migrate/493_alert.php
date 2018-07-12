<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table bb_users_info
add sign_time int not null default 0 comment '签约时间'

html;
Db::query($sql);


$sql=<<<html
alter table bb_users_info
add index sign_time(sign_time)

html;
Db::query($sql);








echo "创建<br>\n";

