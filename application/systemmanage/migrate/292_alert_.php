<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_push_like
add  count int  NOT NULL DEFAULT 1 COMMENT '增加的赞数'
html;
Db::query($sql);





echo "创建<br>\n";
