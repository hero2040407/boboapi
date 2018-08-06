<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race
add has_end tinyint  not null default 0 comment '结束后为1，否则为0，无需后台设置'    
html;
Db::query($sql);





echo "创建<br>\n";
