<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_task_activity
change html_info html_info varchar(8000) not null default '' comment 'html格式活动详情'
html;
Db::query($sql);




echo "创建<br>\n";
