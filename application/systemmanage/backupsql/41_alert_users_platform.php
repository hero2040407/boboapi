<?php

/**
 * 修改bb_users_platform表，加索引
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
alter table bb_users_platform
change uid uid int not null default 0 comment '绑定的uid'
html;
Db::query($sql);


$sql=<<<html
alter table bb_users_platform
change type type tinyint not null default 3 comment '登录类型 1： 微信 2：QQ  3：手机 4：微博'
html;
Db::query($sql);




echo "修改bb_users_platform表<br>\n";

