<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_users_starmaker 
add preference varchar(255) NOT NULL  DEFAULT '' COMMENT '导师偏好，类似1,2'
html;
Db::query($sql);



echo "创建<br>\n";

