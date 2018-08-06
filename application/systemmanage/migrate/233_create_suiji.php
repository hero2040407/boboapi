<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_record_ad (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  record_id int not null default 0 comment '对应短视频表的主键id',
  start_time int not null default 0 comment '广告投放起始时间戳，大于0生效',
  end_time int not null default 0 comment '广告投放起始时间戳，大于0生效',
  set_count int not null default 0 comment '设定的总播放次数',
  play_count int not null default 0 comment '实际播放次数',
  type tinyint not null default 0 comment '1按次数，2按时间，3按时间或次数',
  is_valid tinyint default 1 comment '1有效，0无效广告，默认有效' ,
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='短视频广告表'   
html;
Db::query($sql);



$sql=<<<html
CREATE TABLE bb_record_ad_type (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  record_ad_id int not null default 0 comment '对应短视频广告表的主键id',
  label_id     int not null default 0 comment '对应bb_label表的主键id',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='短视频广告类型表'
html;
Db::query($sql);







echo "创建<br>\n";
