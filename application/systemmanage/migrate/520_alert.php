<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_advise
add audition_time varchar(255) not null default '' comment '临时字段，试镜时间，简短，只有一行文字'
html;
Db::query($sql);

$sql=<<<html
alter table bb_advise
add audition_address varchar(255) not null default '' comment '临时字段，试镜地址，简短，只有一行文字'
html;
Db::query($sql);

$sql=<<<html
alter table bb_advise
add audition_tips varchar(1000) not null default '' comment '临时字段，大段文字，表示试镜须知。'
html;
Db::query($sql);


























echo "创建<br>\n";

