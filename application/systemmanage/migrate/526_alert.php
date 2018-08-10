<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table ds_lunbo
change url url varchar(255) NOT NULL DEFAULT '' COMMENT '点击图片后进入的网址'

html;
Db::query($sql);






echo "创建<br>\n";

