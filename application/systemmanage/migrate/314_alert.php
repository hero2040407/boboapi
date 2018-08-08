<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter TABLE bb_currency 
add shanghu_lottery_count int NOT NULL default 0 comment '已参与的商户抽奖次数'
html;
Db::query($sql);

 


echo "创建<br>\n";
