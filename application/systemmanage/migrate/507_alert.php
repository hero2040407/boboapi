<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table bb_users_agent
add phone varchar(255) not null default '' comment '经纪人电话'

html;
Db::query($sql);


$sql=<<<html
alter table bb_users_agent
add address varchar(255) not null default '' comment '地址。'

html;
Db::query($sql);

$sql=<<<html
alter table bb_users_agent
add info varchar(2550) not null default '' comment '简介。'

html;
Db::query($sql);

$sql=<<<html
alter table bb_users_agent
add is_remove tinyint not null default 0 comment '1删除。0正常。'

html;
Db::query($sql);


$sql=<<<html
alter table bb_baoming_order_prepare
add price_fen int not null default 0 comment '价格，单位分'

html;
Db::query($sql);


$sql=<<<html
alter table bb_baoming_order
add price_fen int not null default 0 comment '价格，单位分'

html;
Db::query($sql);










echo "创建<br>\n";

