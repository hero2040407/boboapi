<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_task_activity 
add has_end tinyint not null default 0 comment '1活动已结束。0未结束，冗余字段排序用'        
html;
Db::query($sql);









echo "创建<br>\n";
