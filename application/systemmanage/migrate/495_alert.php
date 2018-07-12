<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
alter table bb_audition_card_type
add summary varchar(455) not null default '' comment '文字描述，显示在标题下方'

html;
Db::query($sql);










echo "创建<br>\n";

