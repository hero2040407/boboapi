<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table backstage_admin
add is_third_party tinyint not  null default 0 comment '1是第三方自己注册的代理账号。0不是' 
html;
Db::query($sql);




























echo "创建<br>\n";

