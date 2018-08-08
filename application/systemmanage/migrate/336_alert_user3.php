<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_record_comments
add is_robot tinyint not null default 0 comment '1机器人观看，0正常'        
html;
Db::query($sql);












echo "创建<br>\n";
