<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_msg_cache 
add other_record_id int not null  default 0 comment '短视频id，消息里的'
html;
Db::query($sql);






echo "创建<br>\n";
