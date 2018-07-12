<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_register_log
add  qudao_id  int not null default 0 comment '渠道id，老数据没有，新数据有。' 
html;
Db::query($sql);


$sql=<<<html
alter TABLE ds_register_log
add index qudao_id(qudao_id)
html;
Db::query($sql);



















echo "创建<br>\n";

