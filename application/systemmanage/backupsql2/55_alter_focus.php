<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_focus
change uid uid int not null default 0 comment '用户id'        
";

Db::query($sql);

$sql="alter table bb_focus
change focus_uid focus_uid int not null default 0 comment '关注对象用户id'        
";

Db::query($sql);



//
echo "修改bbcy表<br>\n";

