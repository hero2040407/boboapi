<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE backstage_admin
add  parent int not null default 0 comment '代理为0，对于渠道账号为父id，就是他的代理商的id'
html;
Db::query($sql);










echo "创建<br>\n";

