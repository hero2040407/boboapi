<?php

/**
 * 用户地址不可以删除，原因是：被其它表关联，只能假删除
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_address
change  time time int not null default 0 comment '时间戳'
html;
Db::query($sql);

echo "修改用户地址表<br>\n";

