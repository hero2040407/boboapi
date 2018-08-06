<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE bb_baoming 
add msg_content varchar(1500) not null default '' comment '消息的文字内容'
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_baoming
add  index ds_id(ds_id)
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_baoming
add  index msg_id(msg_id)
html;
Db::query($sql);










echo "创建<br>\n";
