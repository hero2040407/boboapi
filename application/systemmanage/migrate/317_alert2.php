<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_comment_public_log 
add record_id int not null default 0 comment '短视频id'        
html;
Db::query($sql);


echo "创建<br>\n";
