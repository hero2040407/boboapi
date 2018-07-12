<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE web_article
add click_count int NOT NULL DEFAULT '0' COMMENT '点击量'
html;
Db::query($sql);














echo "创建<br>\n";

