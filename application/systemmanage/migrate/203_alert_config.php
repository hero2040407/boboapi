<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_config 
add robot_bobi int not null default 0 comment '所有机器人的总帐号，单位波币'        
html;
Db::query($sql);




echo "创建<br>\n";
