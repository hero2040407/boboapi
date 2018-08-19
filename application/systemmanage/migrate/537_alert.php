<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;







$sql=<<<html
alter table ds_register_log
add ticket_count int not null default 0 comment '支持票数'

html;
Db::query($sql);

$sql=<<<html
alter table ds_register_log
add index ticket_count(ticket_count )

html;
Db::query($sql);









echo "创建<br>\n";

