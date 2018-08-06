<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_task_activity
add sub_title varchar(300)  not null default '' comment '2级标题，用于分享页面'    
html;
Db::query($sql);





echo "创建<br>\n";
