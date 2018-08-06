<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_currency
add all_login_time int not null default 0 comment '总登录时间'
";
Db::query($sql);






echo "创建<br>\n";
