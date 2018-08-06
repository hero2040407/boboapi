<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql="
alter table bb_alitemp
add realurl varchar(255) not null default '' comment '真的url'
";
Db::query($sql);


echo "创建<br>\n";

