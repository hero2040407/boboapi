<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_push_like
change time time int   NOT NULL DEFAULT 0 COMMENT '创建时间'
html;
Db::query($sql);





echo "创建<br>\n";
