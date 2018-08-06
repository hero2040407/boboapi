<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter TABLE bb_record_invite_starmaker_fail 
add   reason varchar(1000) not null default '' comment '管理员填写的失败理由'
html;
Db::query($sql);


echo "创建<br>\n";
