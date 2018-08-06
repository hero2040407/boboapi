<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_money_prepare 
add openid varchar(255) not null default '' comment '对应服务号的openid'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_money_prepare
add index  openid(openid)
html;
Db::query($sql);







echo "创建<br>\n";
