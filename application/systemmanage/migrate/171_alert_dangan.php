<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
alter TABLE ds_money_prepare 
add index  third_serial(third_serial)
html;
Db::query($sql);









echo "创建<br>\n";
