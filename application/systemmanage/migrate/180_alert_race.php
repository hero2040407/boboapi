<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_race 
add qrcode_url varchar(255) not null default '' comment '二维码对应的网址'
html;
Db::query($sql);

$sql=<<<html
alter TABLE ds_race
add qrcode_file varchar(255) not null default '' comment '二维码文件，例如 /public/1.jpg'
html;
Db::query($sql);





echo "创建<br>\n";
