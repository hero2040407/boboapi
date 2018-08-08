<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_tongji_huizong
add renzheng_rate decimal(10,3)  not null default 0 comment '个人认证成功率'    
html;
Db::query($sql);







echo "创建<br>\n";
