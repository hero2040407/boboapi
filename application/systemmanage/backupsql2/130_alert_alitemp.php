<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql="
alter table bb_alitemp
add uid int not null default 0 comment 'uid'
";
Db::query($sql);


echo "创建<br>\n";

