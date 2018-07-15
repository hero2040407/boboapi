<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_baoming_order_prepare
add json_parameter varchar(1000) not null default '' comment 'json格式的其他参数'

html;
Db::query($sql);



$sql=<<<html
alter table bb_baoming_order
add json_parameter varchar(1000) not null default '' comment 'json格式的其他参数'

html;
Db::query($sql);












echo "创建<br>\n";

