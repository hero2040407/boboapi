<?php

/**
 * bb_shop_goods 表
 * 加创建时间和更新时间2个字段
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_shop_goods
add create_time int not null default 0 comment '创建时间'
";
Db::query($sql);


$sql="alter table bb_shop_goods
add update_time int not null default 0 comment '最后修改时间'
";

Db::query($sql);


echo "修改商品表，加创建时间和更新时间2个字段<br>\n";

