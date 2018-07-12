<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter TABLE  bb_advise 
add pic varchar(255) not null default '' comment '通告的图片'

html;
Db::query($sql);






echo "创建<br>\n";

