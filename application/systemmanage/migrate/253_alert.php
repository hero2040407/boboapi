<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter TABLE bb_msg_user_config 
add sort int not null default 0 comment '顺序'
html;
Db::query($sql);






echo "创建<br>\n";
