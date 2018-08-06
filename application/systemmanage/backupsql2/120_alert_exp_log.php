<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql="
alter table bb_users_exp
add exp_all int not null default 0 comment '总经验'
";
Db::query($sql);

echo "创建<br>\n";
