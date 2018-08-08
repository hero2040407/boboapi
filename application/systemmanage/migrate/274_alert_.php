<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter TABLE bb_task_activity 
add html_info varchar(2000)  NOT NULL DEFAULT '' COMMENT 'html富文本表示的内容'
html;
Db::query($sql);




echo "创建<br>\n";
