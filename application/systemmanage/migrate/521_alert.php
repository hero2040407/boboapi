<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_advise_join
add  unique advise_id_role_id(advise_id, uid)
html;
Db::query($sql);




























echo "创建<br>\n";

