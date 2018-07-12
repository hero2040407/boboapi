<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE backstage_auth
add auth_id int NOT NULL DEFAULT '0' COMMENT '权限id'
html;
Db::query($sql);


$sql=<<<html
alter TABLE backstage_auth
add index auth_id(auth_id)
html;
Db::query($sql);













echo "创建<br>\n";

