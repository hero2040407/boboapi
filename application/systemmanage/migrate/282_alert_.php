<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_shop_goods
add  exchange_score  int not null default 0 comment '所需兑换的积分'
html;
Db::query($sql);



echo "创建<br>\n";
