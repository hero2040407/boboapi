<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter table ds_like
add type tinyint not null default 1 comment '1普通投票，2分享投票，3波币购买投票'

html;
Db::query($sql);


$sql=<<<html
alter table ds_like
add index type (type)

html;
Db::query($sql);

 



echo "创建<br>\n";

