<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter table bb_brandshop_application
add  uid int not null default 0 comment 'uid'
html;
Db::query($sql);

$sql=<<<html
alter table bb_brandshop_application
add  index uid(uid)
html;
Db::query($sql);


echo "创建<br>\n";
