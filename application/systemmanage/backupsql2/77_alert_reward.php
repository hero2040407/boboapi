<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="alter table bb_user_activity
drop index record_id
";
Db::query($sql);

$sql="alter table bb_user_activity
drop index room_id
";
Db::query($sql);


// create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
// has_reward tinyint(4) NOT NULL DEFAULT '0' COMMENT '1已领奖，0未领奖',
// reward_count int(11) NOT NULL DEFAULT '0' COMMENT '领取的波币数',
// reward_time int(11) NOT NULL DEFAULT '0' COMMENT '领奖时间',
// paiming int(11) NOT NULL DEFAULT '0' COMMENT '排名，1最大',
// record_id int(11) NOT NULL DEFAULT '0' COMMENT '短视频id',
// room_id varchar(255) NOT NULL DEFAULT '0' COMMENT '房间id',
// like_count int(11) NOT NULL DEFAULT '0' COMMENT '赞数',

$sql="alter table bb_user_activity
drop column has_reward
";
Db::query($sql);

$sql="alter table bb_user_activity
drop column reward_count
";
Db::query($sql);

$sql="alter table bb_user_activity
drop column reward_time
";
Db::query($sql);

$sql="alter table bb_user_activity
drop column paiming
";
Db::query($sql);

$sql="alter table bb_user_activity
drop column record_id
";
Db::query($sql);

$sql="alter table bb_user_activity
drop column room_id
";
Db::query($sql);

$sql="alter table bb_user_activity
drop column like_count
";
Db::query($sql);



echo "创建<br>\n";
