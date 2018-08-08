<?php

/**
 * bb_shop_order 表
 * 修改订单表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_shop_order
add logistics_company varchar(255) not null default '' comment '物流公司代号，如SF' 
html;


Db::query($sql);


$sql=<<<html
alter table bb_shop_order
add logistics_is_subscribe tinyint not null default 0 comment '物流单号轨迹是否订阅，1已订阅'
html;

Db::query($sql);


$sql=<<<html
alter table bb_shop_order
add logistics_state tinyint not null default 0 
   comment '当前的物流单号轨迹状态，0-无轨迹，2-在途中,3-签收,4-问题件'
html;

Db::query($sql);



echo "修改订单表<br>\n";

