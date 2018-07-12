<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_brandshop
add phone varchar(255) not null default '' comment '机构手机，客服联系用' 
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_brandshop
add index phone(phone)
html;
Db::query($sql);




















echo "创建<br>\n";

