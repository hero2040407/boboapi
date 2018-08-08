<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_currency
add caifu_ranking tinyint not null default 0 comment '财富上榜是否已通知'
";
Db::query($sql);

$sql="alter table bb_currency
add  lv_ranking  tinyint not null default 0  comment '等级上榜是否已通知'
";
Db::query($sql);

$sql="alter table bb_currency
add  fensi_ranking  tinyint not null default 0  comment '粉丝上榜是否已通知'
";
Db::query($sql);




echo "创建<br>\n";
