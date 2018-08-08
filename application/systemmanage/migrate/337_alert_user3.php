<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_users_starmaker
add detail_jieshao varchar(2000) not null default '' comment '单独详情页，星推官介绍栏'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_users_starmaker
add detail_huojiang varchar(1000) not null default '' comment '单独详情页，获奖介绍栏'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_users_starmaker
add detail_shiyi varchar(255) not null default '' comment '单独详情页，适宜星势力栏'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_users_starmaker
add detail_yinxiang varchar(255) not null default '' comment '单独详情页，星推官印象栏'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_users_starmaker
add detail_shangxian varchar(255) not null default '' comment '单独详情页，上线时间栏，这是汉字的说明，任意的，而week字段是规则的'
html;
Db::query($sql);






echo "创建<br>\n";
