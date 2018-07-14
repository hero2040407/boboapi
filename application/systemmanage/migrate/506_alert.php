<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table bb_users_updates
add status tinyint not null default 0 comment '0未审核，1已审核，2审核失败'

html;
Db::query($sql);


$sql=<<<html
alter table bb_users_updates
add index status(status)

html;
Db::query($sql);












echo "创建<br>\n";

