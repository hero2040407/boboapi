<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter TABLE lt_roulette 
add type tinyint NOT NULL default 0 comment '1每日签到转盘，2商户邀请。'
html;
Db::query($sql);

 


echo "创建<br>\n";
