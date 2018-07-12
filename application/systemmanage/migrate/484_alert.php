<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_users_info
add has_sign tinyint not null default 0 comment '1是签约童星，0vip童星'
html;
Db::query($sql);











echo "创建<br>\n";

