<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql="
alter table bb_push
change push_url push_url varchar(255) not null default '' comment '推流地址'
";
Db::query($sql);

$sql="
alter table bb_push
change pull_url pull_url varchar(255) not null default '' comment '拉流地址'
";
Db::query($sql);

$sql="
alter table bb_push
change space_name space_name varchar(255) not null default '' comment '空间名称'
";
Db::query($sql);

$sql="
alter table bb_push
change stream_name stream_name varchar(255) not null default '' comment '流名称'
";
Db::query($sql);



echo "创建<br>\n";

