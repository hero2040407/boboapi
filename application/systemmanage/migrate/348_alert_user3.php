<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
use BBExtend\Sys;

$sql=<<<html
alter table bb_task_activity
add  brandshop_id int not null default 0 comment '对应的品牌馆id，一个邀约，只能属于一个品牌馆，为0表示是我公司官方活动'
html;
Db::query($sql);

$sql=<<<html
alter table bb_task_activity
add  index brandshop_id(brandshop_id)
html;
Db::query($sql);



$sql=<<<html
alter table ds_race
add  brandshop_id int not null default 0 comment '对应的品牌馆id，一个大赛，只能属于一个品牌馆，为0表示是品牌馆以外 的大赛主办方'
html;
Db::query($sql);

$sql=<<<html
alter table ds_race
add  index brandshop_id(brandshop_id)
html;
Db::query($sql);










echo "创建<br>\n";
