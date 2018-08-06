<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter table bb_present
add  money_fen int not null default 0 comment '现金价格，单位是分。'
html;
Db::query($sql);



echo "创建<br>\n";
