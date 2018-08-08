<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_users_platform 
add original varchar(255) not null default '' comment '原始id，原始凭证'        
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_users_platform
add index original(original)
html;
Db::query($sql);








echo "创建<br>\n";
