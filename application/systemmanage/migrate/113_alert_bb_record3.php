<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
alter table bb_record
add dashang_bean_all int not null default 0 comment '打赏波豆总额'
";
Db::query($sql);


$sql="
alter table bb_push
add dashang_bean_all int not null default 0 comment '打赏波豆总额'
";
Db::query($sql);

$sql="
alter table bb_rewind
add dashang_bean_all int not null default 0 comment '打赏波豆总额'
";
Db::query($sql);













echo "创建<br>\n";
