<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_record
add  share_count int not null default 0 comment '分享次数'        
html;
Db::query($sql);




echo "创建<br>\n";
