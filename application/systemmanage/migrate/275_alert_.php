<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
alter table bb_config
add share_server varchar(255) not null default '' comment '分享地址的域名，防止电信广告插入'
html;
Db::query($sql);




echo "创建<br>\n";
