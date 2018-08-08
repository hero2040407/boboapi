<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_task_activity
add pk_statistics_json varchar(1000)  not null default '' comment 'pk发奖时的统计结果，用json字符串表示，包括red_count人数,red_like赞数,red_score积分,和蓝方的3个。'
html;
Db::query($sql);










echo "创建<br>\n";

