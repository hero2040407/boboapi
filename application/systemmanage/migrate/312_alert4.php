<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter TABLE bb_users_signin_log 
add   unique uid_datestr(uid,datestr)
html;
Db::query($sql);


echo "创建<br>\n";
