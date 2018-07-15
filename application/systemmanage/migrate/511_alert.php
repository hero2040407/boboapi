<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_audition_card
add bind_time int not null default 0 comment '绑定时间'

html;
Db::query($sql);














echo "创建<br>\n";

