<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter table bb_brandshop
add  application_id int not null default 0 comment '对应的申请资料表的id，最好要设置这个值'
html;
Db::query($sql);

$sql=<<<html
alter table bb_brandshop
add  index application_id(application_id)
html;
Db::query($sql);


echo "创建<br>\n";
