<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_users_starmaker 
add html_info  varchar(3000) not null default '' comment 'h5页面的介绍'        
html;
Db::query($sql);











echo "创建<br>\n";
