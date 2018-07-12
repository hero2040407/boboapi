<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table bb_tongji_huizong
add huoyue_count int not null default 0 comment '活跃用户数'

html;
Db::query($sql);






echo "创建<br>\n";

