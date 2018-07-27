<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_advise
add max_join_number int not  null default 0 comment '0不限制，大于0则表示最多参加人数。' 
html;
Db::query($sql);




























echo "创建<br>\n";

