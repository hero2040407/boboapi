<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_audition_card
add lock_version int not null default 0 comment '乐观锁字段，后台不需处理。'

html;
Db::query($sql);














echo "创建<br>\n";

