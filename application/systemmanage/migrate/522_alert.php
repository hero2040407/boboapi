<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;





$sql=<<<html
alter table bb_audition_card_type
add serial_ab char(2) not null default '' comment '缩写，也是试镜卡序列号头两位字母，必须大写，且每个类型都应有唯一的缩写' 
html;
Db::query($sql);




























echo "创建<br>\n";

