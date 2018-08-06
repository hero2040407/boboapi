<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter TABLE bb_push 
add domain varchar(255) not null default '' comment '我们设定的推送域名'
html;
Db::query($sql);



$sql=<<<html
alter TABLE bb_push
add index domain(domain)
html;
Db::query($sql);




echo "创建<br>\n";
