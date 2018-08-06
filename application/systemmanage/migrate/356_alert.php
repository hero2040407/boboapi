<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE bb_record_invite_starmaker 
add push_type tinyint not null default 1 comment '1指定某个导师邀请，2抢单模式邀请'
html;
Db::query($sql);



echo "创建<br>\n";
