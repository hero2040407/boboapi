<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_register_log 
add phone char(11) not null default '' comment '手机号' 
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_register_log
add index phone(phone)
html;
Db::query($sql);



echo "创建<br>\n";
