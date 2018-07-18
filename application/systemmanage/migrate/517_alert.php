<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_users_card_material
add baidu_citycode varchar(255) not null default  '' comment '百度城市代码'

html;
Db::query($sql);



















echo "创建<br>\n";

