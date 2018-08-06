<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_record
add real_like  int not null default 0 comment '真实点赞数'        
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_record
add real_people  int not null default 0 comment '真实浏览量'
html;
Db::query($sql);





echo "创建<br>\n";
