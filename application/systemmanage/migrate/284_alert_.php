<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_users_invite_register
change phone phone varchar(255) not null default '' comment '手机号'
html;
Db::query($sql);

// $sql=<<<html
// alter table bb_record_like
// add index time(time)
// html;
// Db::query($sql);



echo "创建<br>\n";
