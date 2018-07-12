<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table bb_toppic
add table_name varchar(255) not null default '' comment '表名'

html;
Db::query($sql);

$sql=<<<html
alter table bb_toppic
add foreign_id int not null default 0 comment '键的id'

html;
Db::query($sql);


$sql=<<<html
alter table bb_toppic
add index table_name(table_name)

html;
Db::query($sql);

$sql=<<<html
alter table bb_toppic
add index foreign_id(foreign_id)

html;
Db::query($sql);







echo "创建<br>\n";

