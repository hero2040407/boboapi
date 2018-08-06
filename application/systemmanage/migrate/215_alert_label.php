<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race
add is_success_jump tinyint  not null default 0 comment '报名成功后是否跳转到页面，1是，0不是'    
html;
Db::query($sql);







echo "创建<br>\n";
