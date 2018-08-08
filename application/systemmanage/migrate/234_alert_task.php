<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_toppic
add picpath_pad varchar(255)  not null default '' comment '平板电脑的图片路径'  
html;
Db::query($sql);


echo "创建<br>\n";
