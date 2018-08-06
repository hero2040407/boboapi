<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_record_invite_starmaker
add  answer_time int not null default 0 comment '点评时间'
html;
Db::query($sql);


$sql=<<<html
alter table bb_record_invite_starmaker
add  zan_count int not null default 0 comment '导师增加的赞数'
html;
Db::query($sql);






echo "创建<br>\n";
