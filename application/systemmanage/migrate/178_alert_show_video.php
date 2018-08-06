<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE ds_show_video (
  id int(11) NOT NULL AUTO_INCREMENT,
  ds_id int(11) NOT NULL DEFAULT '0' COMMENT '大赛id',
  room_id  varchar(255) not null default '' comment '房间id',
  video_id int not null default 0 comment '视频id',
  uid int not null default 0 comment '用户id',
  type tinyint not null default 1 comment '1直播，2短视频',
  create_time int not null default 0 comment '创建时间',
  PRIMARY KEY (id),
  index uid (uid),
  index ds_id (ds_id),
  index video_id (video_id),
  index room_id(room_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='大赛展示用视频表，含直播和录播'
html;
Db::query($sql);




echo "创建<br>\n";
