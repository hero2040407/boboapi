<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_request 
add domain varchar(255) NOT NULL  DEFAULT '' COMMENT '域名'
html;
Db::query($sql);



echo "创建<br>\n";

