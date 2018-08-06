<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race 
add register_reward_1 int not null default 0 comment '通过大赛注册新帐号奖励的波币数。'
html;
Db::query($sql);


$sql=<<<html
alter TABLE ds_race 
add register_reward_2 int not null default 0 comment '大赛支付报名费成功奖励的波币数。'
html;
Db::query($sql);




echo "创建<br>\n";
