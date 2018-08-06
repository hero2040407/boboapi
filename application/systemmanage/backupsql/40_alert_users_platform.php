<?php

/**
 * 修改bb_users_platform表，加索引
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_users_platform
add index uid(uid)
html;
Db::query($sql);




echo "修改bb_users_platform表<br>\n";

