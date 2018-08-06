<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_users_starmaker 
add is_show tinyint not null default 1 comment '1被展示，0被隐藏，正常是1'        
html;
Db::query($sql);












echo "创建<br>\n";
