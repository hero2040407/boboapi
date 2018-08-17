<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;







$sql=<<<html
alter table ds_register_log
add age int not null default 0 comment '冗余字段，年龄：计算方式 当前年- 出生年'

html;
Db::query($sql);

$sql=<<<html
alter table ds_register_log
add index age(age)

html;
Db::query($sql);










echo "创建<br>\n";

