<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE bb_tongji_huizong
add money1 decimal(10,2)  not null default 0 comment 'bo币消费总额'    
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_tongji_huizong
add money2 decimal(10,2)  not null default 0 comment 'bo币获取数'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_tongji_huizong
add money3 decimal(10,2)  not null default 0 comment 'bo豆提现数'
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_tongji_huizong
add money4 decimal(10,2)  not null default 0 comment '充值金额，元。'
html;
Db::query($sql);








echo "创建<br>\n";
