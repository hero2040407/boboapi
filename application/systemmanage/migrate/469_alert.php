<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE backstage_auth
drop column module
html;
Db::query($sql);

$sql=<<<html
alter TABLE backstage_auth
drop column name
html;
Db::query($sql);

$sql=<<<html
alter TABLE backstage_auth
drop column module_key
html;
Db::query($sql);


















echo "创建<br>\n";

