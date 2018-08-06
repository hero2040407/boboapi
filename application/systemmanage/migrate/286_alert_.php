<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_currency_log
add msg_type int not null default 0 comment '事件类型，和消息类型保持完全一致，便于检索用'
html;
Db::query($sql);

$sql=<<<html
alter table bb_currency_log
add index msg_type(msg_type)
html;
Db::query($sql);




echo "创建<br>\n";
