<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
alter TABLE bb_tixian_apply
add unionid varchar(255) not null default ''  comment '微信统一id'
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_tixian_apply
add index unionid(unionid)
html;
Db::query($sql);

$sql=<<<html
alter TABLE bb_tixian_apply
add index openid(openid)
html;
Db::query($sql);











echo "创建<br>\n";
