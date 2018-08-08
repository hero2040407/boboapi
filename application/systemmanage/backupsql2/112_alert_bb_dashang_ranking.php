<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_dashang_ranking
add bean_all int not null default 0 comment '波豆总额'
";
Db::query($sql);













echo "创建<br>\n";
