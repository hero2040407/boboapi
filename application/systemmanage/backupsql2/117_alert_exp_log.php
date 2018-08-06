<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_users_exp_log
add index uid(uid)
";
Db::query($sql);

$sql="
alter table bb_users_exp_log
add index type(type)
";
Db::query($sql);

$sql="
alter table bb_users_exp_log
add index who_uid(who_uid)
";
Db::query($sql);












echo "创建<br>\n";
