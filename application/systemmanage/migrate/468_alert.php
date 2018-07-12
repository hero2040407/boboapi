<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE backstage_auth
drop index module_module_key
html;
Db::query($sql);
















echo "创建<br>\n";

