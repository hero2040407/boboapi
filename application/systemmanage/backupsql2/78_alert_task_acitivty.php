<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_task_activity
change is_send_reward is_send_reward tinyint not null default 0 comment '是否发过奖励，1发过'
";
Db::query($sql);



echo "创建<br>\n";
