<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_users_card
add baidu_citycode varchar(255) not null default  '' comment '百度城市代码'

html;
Db::query($sql);



$sql=<<<html
alter table bb_record
add baidu_citycode varchar(255) not null default  '' comment '百度城市代码'

html;
Db::query($sql);

$sql=<<<html
alter table bb_users_updates
add baidu_citycode varchar(255) not null default  '' comment '百度城市代码'

html;
Db::query($sql);

$sql=<<<html
alter table bb_users_updates
add index baidu_citycode( baidu_citycode )

html;
Db::query($sql);

















echo "创建<br>\n";

