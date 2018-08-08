<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql="
alter table bb_users_exp_log
add typeint int not null default 0 comment '加经验类型的整数，较多需看程序extend/BBExtend/Level.php'
";
Db::query($sql);

$sql="
alter table bb_users_exp_log
add index typeint(typeint)
";
Db::query($sql);












echo "创建<br>\n";
