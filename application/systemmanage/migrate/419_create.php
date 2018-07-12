<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_aliyun_record (
  id int(11) unsigned NOT NULL AUTO_INCREMENT,
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  video_path varchar(255) NOT NULL DEFAULT '' COMMENT '阿里云回调中的视频地址，是老地址。',
  PRIMARY KEY (id),
  index video_path( video_path )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='阿里云短视频暂存表'
html;
Db::query($sql);



echo "创建<br>\n";

