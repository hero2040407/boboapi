<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_toppic
drop column table_name

html;
Db::query($sql);


$sql=<<<html
alter table bb_toppic
add module_name varchar(255) not null default '' comment '轮播图模块'

html;
Db::query($sql);

$sql=<<<html
alter table bb_toppic
add index module_name(module_name)


html;
Db::query($sql);
















echo "创建<br>\n";

