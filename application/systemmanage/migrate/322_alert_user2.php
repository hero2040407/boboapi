<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_dashang_prepare 
change room_id room_id  varchar(255) not null default '' comment '视频的room_id'        
html;
Db::query($sql);









echo "创建<br>\n";
