<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table bb_advise
add pic2 varchar(255) not null default '' comment '封面图，小一些，竖着放。'

html;
Db::query($sql);










echo "创建<br>\n";

