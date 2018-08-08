<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_shop_goods
add unreal_sell_num int not null default 0 comment '假销量，用于客户端显示'
html;
Db::query($sql);






echo "创建<br>\n";
