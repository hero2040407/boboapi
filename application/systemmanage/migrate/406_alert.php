<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_users_card_template_material
drop column uid 
html;
Db::query($sql);



















echo "创建<br>\n";

