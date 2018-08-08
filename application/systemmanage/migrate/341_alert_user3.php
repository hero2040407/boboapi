<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_oss
add old_id int not null default 0 comment '原表的id'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_oss
add index old_id(old_id)
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_oss
add index old_table(old_table)
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_oss
add index new_value(new_value)
html;
Db::query($sql);








echo "创建<br>\n";
