<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_dashang_day_ranking (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL DEFAULT '0' COMMENT '打赏人，是付钱的人',
  movie_id int(11) NOT NULL DEFAULT '0' COMMENT '被打赏视频id',
  target_uid int(11) NOT NULL DEFAULT '0' COMMENT '被打赏人uid',
  update_time int(11) NOT NULL DEFAULT '0' COMMENT '当前时间',
  gold_all int(11) NOT NULL DEFAULT '0' COMMENT '打赏这个视频的波币总数',
  room_id varchar(255) NOT NULL DEFAULT '' COMMENT '房间id，所有视频唯一',
  bean_all int(11) NOT NULL DEFAULT '0' COMMENT '波豆总额',
  daystr char(8) not null default '' comment '日期例如20170101',
  PRIMARY KEY (id),
  KEY movie_id (movie_id),
  KEY target_uid (target_uid),
  KEY room_id (room_id),
  key daystr (daystr)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='打赏日榜'
html;
Db::query($sql);


echo "创建<br>\n";
