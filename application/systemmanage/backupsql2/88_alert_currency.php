<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_currency
add has_duihuan tinyint not null default 0 comment '兑换功能是否已通知'
";
Db::query($sql);






echo "创建<br>\n";
