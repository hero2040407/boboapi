<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter table ds_money_prepare
add type tinyint not null default 1 comment '1大赛报名费，2大赛打赏给其他报名者'

html;
Db::query($sql);



 



echo "创建<br>\n";

