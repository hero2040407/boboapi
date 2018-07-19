<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_advise_join
drop column audition_cart_id
html;
Db::query($sql);

$sql=<<<html
alter table bb_advise_join
add audition_card_id int not null default 0 comment '试镜卡id'
html;
Db::query($sql);





















echo "创建<br>\n";

