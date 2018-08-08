<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_users
add constellation varchar(255)  not null default '' comment '星座'    
html;
Db::query($sql);



$sql=<<<html
alter TABLE bb_currency
drop column signature 
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_currency
drop column constellation
html;
Db::query($sql);






echo "创建<br>\n";
