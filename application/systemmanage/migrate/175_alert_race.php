<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race 
add area1_name varchar(255) not null default '' comment '省的名称，'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race
add area2_name varchar(255) not null default '' comment '市的名称，没有就不填'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race
add area3_name varchar(255) not null default '' comment '区的名称，没有就不填'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race
add index area1_name(area1_name)
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race
add index area2_name(area2_name)
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race
add index area3_name(area3_name)
html;
Db::query($sql);








echo "创建<br>\n";
