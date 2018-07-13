<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
CREATE TABLE bb_users_updates_media (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  bb_users_updates_id int not null default 0 comment '动态id，对应bb_users_updates_表主键',
  type tinyint not null default 0 comment '1文字，2图片，3视频，4模卡',
  word             varchar(9000)   not null default '' comment 'type=1有效，文字',
  pic_width   int NOT NULL DEFAULT 0 COMMENT 'type=2有效，图片宽度',
  pic_height  int NOT NULL DEFAULT 0 COMMENT 'type=2有效，图片高度',
  bb_record_id      int not null default 0 comment 'type=3有效，对应bb_record表的主键。',
  time_length varchar(255) not null default '' comment 'type=3有效，类似01:15，或02:30:11，必须英文冒号',
  url varchar(255) not null default '' comment 'type=2和3有效，图片和视频的网址，',
  bb_users_card_id int not null default 0 comment 'type=4有效，对应bb_users_card表的主键。',
  PRIMARY KEY (id),
  index bb_users_updates_id (bb_users_updates_id),
  index bb_record_id(bb_record_id),
  index bb_users_card_id(bb_users_card_id),
  index type(type)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='用户动态表'

html;
Db::query($sql);






echo "创建<br>\n";

