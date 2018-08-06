<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE lt_user_task
add create_time int not null default 0 comment '创建时间'  
html;
Db::query($sql);



$sql=<<<html
alter TABLE lt_user_task
drop column craete_time
html;
Db::query($sql);






echo "创建<br>\n";
