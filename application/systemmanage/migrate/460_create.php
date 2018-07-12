<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter TABLE   ds_race_message 
add content varchar(3000) not null default '' comment '消息内容'
html;
Db::query($sql);



echo "创建<br>\n";

