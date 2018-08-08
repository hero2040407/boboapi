<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_currency
add constellation varchar(255)  not null default '' comment '星座'    
html;
Db::query($sql);



$sql=<<<html
alter TABLE bb_currency
add signature varchar(400)  not null default '' comment '个性签名'
html;
Db::query($sql);






echo "创建<br>\n";
