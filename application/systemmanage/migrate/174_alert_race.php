<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race 
add area_id int not null default 0 comment '所在地区的id，对于bb_area表的主键。'
html;
Db::query($sql);









echo "创建<br>\n";
