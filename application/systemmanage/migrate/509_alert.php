<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_advise
add h5_info varchar(3000) not null default '' comment '通告h5详情，姜雯确定，是html富文本，有图有文'

html;
Db::query($sql);














echo "创建<br>\n";

