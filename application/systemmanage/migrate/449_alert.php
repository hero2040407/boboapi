<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE backstage_admin
add pwd_original varchar(255) not null default '' comment '密码明文'
html;
Db::query($sql);









echo "创建<br>\n";

