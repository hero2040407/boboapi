<?php

/**
 * bb_cu
 * 
 * 
 * xieye
 */

use think\Db;
$sql="alter table bb_user_activity
add has_reward tinyint not null default 0 comment '1已领奖，0未领奖'
";
Db::query($sql);

$sql="alter table bb_user_activity
add reward_count int not null default 0 comment '领取的波币数'
";
Db::query($sql);

$sql="alter table bb_user_activity
add reward_time int not null default 0 comment '领奖时间'
";
Db::query($sql);





//
echo "修改bbcy表<br>\n";

