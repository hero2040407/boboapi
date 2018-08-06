<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
alter TABLE bb_tongji_huizong
add money_dashang_zhibo int NOT NULL DEFAULT '0' COMMENT '打赏直播BO币'
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_tongji_huizong
add money_dashang_record int NOT NULL DEFAULT '0' COMMENT '打赏短视频BO币'
html;
Db::query($sql);


$sql=<<<html
alter TABLE bb_tongji_huizong
add money_dashang_fenxiang int NOT NULL DEFAULT '0' COMMENT '短视频分享打赏'
html;
Db::query($sql);



$sql=<<<html
alter TABLE bb_tongji_huizong
add push_fayan_count int NOT NULL DEFAULT '0' COMMENT '观看直播发言人数'
html;
Db::query($sql);








echo "创建<br>\n";

