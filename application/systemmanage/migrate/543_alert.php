<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter table ds_money_prepare
add json_info varchar(1000) not null default '' comment 'js信息保存的信息，目前用于大赛打赏'

html;
Db::query($sql);



 



echo "创建<br>\n";

