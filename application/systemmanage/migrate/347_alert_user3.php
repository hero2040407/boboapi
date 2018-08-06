<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
use BBExtend\Sys;

$sql=<<<html
alter table bb_users_starmaker
add  brandshop_id int not null default 0 comment '对应的品牌馆id，一个星推官即导师，只能属于一个品牌馆，或者不属于任何品牌馆独立存在。'
html;
Db::query($sql);

$sql=<<<html
alter table bb_users_starmaker
add  index brandshop_id(brandshop_id)
html;
Db::query($sql);












echo "创建<br>\n";
