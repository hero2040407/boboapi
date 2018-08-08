<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_users
change vip vip tinyint  not null default 0 comment '1是vip，0不是'        
";

Db::query($sql);


$sql="alter table bb_users
change vip_time vip_time int  not null default 0 comment 'vip到期时间，时间戳'
";

Db::query($sql);




//
echo "修改bbcy表<br>\n";

