<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_user_activity_reward
add has_message tinyint not null default 0 comment '1发过消息，0未发'
";
Db::query($sql);


echo "创建<br>\n";
