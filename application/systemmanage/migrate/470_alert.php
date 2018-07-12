<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE backstage_auth
add index roles(roles)
html;
Db::query($sql);

$sql=<<<html
alter TABLE backstage_auth
add unique xx(roles, auth_id)
html;
Db::query($sql);



















echo "创建<br>\n";

