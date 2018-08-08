<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_tongji_huizong_register
add name_ios int  not null default 0 comment 'ios注册量'    
html;
Db::query($sql);





echo "创建<br>\n";
