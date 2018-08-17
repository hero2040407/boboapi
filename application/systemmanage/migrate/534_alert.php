<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter table ds_dangan_config
drop column info

html;
Db::query($sql);


$sql=<<<html
alter table ds_dangan_config
add options varchar(1000) not null default '' comment '适用于类型是复选，单选，下拉的情况，使用英文逗号分隔的字符串，例如青年,少年,儿童 '

html;
Db::query($sql);











echo "创建<br>\n";

