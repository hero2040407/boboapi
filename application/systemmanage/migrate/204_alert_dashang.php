<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_dashang_log
add is_robot  tinyint not null default 0 comment '0正常，1机器人打赏'        
html;
Db::query($sql);




echo "创建<br>\n";
