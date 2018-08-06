<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter table bb_shanghu
change qrcode logo varchar(255) not null default '' comment '商户logo'
html;
Db::query($sql);



echo "创建<br>\n";
