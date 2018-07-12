<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_brandshop_application
add pic varchar(255) not null default '' comment '机构申请时的照片' 
html;
Db::query($sql);




















echo "创建<br>\n";

