<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql="
alter table bb_currency_log
change time time int not null default 0 comment '创建时间'
";
Db::query($sql);



echo "创建<br>\n";

