<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE ds_record
drop column qudao_id
html;
Db::query($sql);





















echo "创建<br>\n";

