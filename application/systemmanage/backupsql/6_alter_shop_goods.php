<?php

/**
 * bb_shop_goods 表
 * 加实际折扣字段：原先的discount是后台管理员手动填写的。
 *    而实际折扣字段的值，假如当前在促销期间，值应该等同折扣字段，假如不在促销期间，值＝10
 * 加促销起始时间
 * 加促销结束时间
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_shop_goods
add real_discount int not null default 10 comment '真实折扣1－10，已考虑了促销期间，系统自动更新'        
";

Db::query($sql);

$sql="alter table bb_shop_goods
add on_sale_start_time int not null default 0 comment '促销起始时间，时间戳'
";

Db::query($sql);

$sql="alter table bb_shop_goods
add on_sale_end_time int not null default 0 comment '促销结束时间，时间戳'
";

Db::query($sql);



//
echo "修改临时订单表<br>\n";

