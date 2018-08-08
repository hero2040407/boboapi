<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_buy
add money decimal(10,2)  not null default 0 comment '消费人民币，元。'    
html;
Db::query($sql);







echo "创建<br>\n";
