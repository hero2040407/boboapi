<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter TABLE bb_users 
add  not_login tinyint  NOT NULL DEFAULT 0 COMMENT '0正常，1禁止登录'
html;
Db::query($sql);




echo "创建<br>\n";
