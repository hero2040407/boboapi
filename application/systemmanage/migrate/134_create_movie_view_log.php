<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_moive_view_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  uid int(11) NOT NULL DEFAULT '0' COMMENT '打赏人，是付钱的人',
  movie_id int(11) NOT NULL DEFAULT '0' COMMENT '被打赏视频id',
  target_uid int(11) NOT NULL DEFAULT '0' COMMENT '被打赏人uid',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '当前时间',
  PRIMARY KEY (id),
  KEY movie_id (movie_id),
  key uid (uid),
  KEY target_uid (target_uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='观看视频日志'
html;
Db::query($sql);


echo "创建<br>\n";
