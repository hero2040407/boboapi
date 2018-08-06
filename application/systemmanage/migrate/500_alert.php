<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table bb_advise
add is_recommend tinyint not null default 0 comment '1后台推荐的，0普通'

html;
Db::query($sql);












echo "创建<br>\n";

