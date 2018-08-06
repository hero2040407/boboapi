<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter TABLE bb_msg_answer 
add has_process tinyint not null default 0 comment '1处理过，0未处理'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_msg_answer
add process_name varchar(255) not null default '' comment '管理员名称'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_msg_answer
add process_info varchar(1000) not null default '' comment '处理意见'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_msg_answer
add process_time int not null default 0 comment '处理时间'
html;
Db::query($sql);







echo "创建<br>\n";
