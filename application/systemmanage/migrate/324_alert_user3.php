<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_shanghu 
add invite_count  int not null default 0 comment '这是邀请总数，无论被邀请人是否注册'        
html;
Db::query($sql);









echo "创建<br>\n";
