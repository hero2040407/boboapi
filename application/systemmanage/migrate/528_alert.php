<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table ds_register_log
add register_info  varchar(5000) NOT NULL DEFAULT '' COMMENT '报名信息的json格式文本，包括全部的，例如{"身高":"177"，“手机”:"1222222","城市":"南京"}'

html;
Db::query($sql);






echo "创建<br>\n";

