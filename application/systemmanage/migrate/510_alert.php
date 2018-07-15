<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_advise_role
add identity varchar(255) not null default '' comment '身份，例如男主角'

html;
Db::query($sql);














echo "创建<br>\n";

