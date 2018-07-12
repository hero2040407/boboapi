<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql=<<<html
CREATE TABLE bb_theme (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  title varchar(255) not null default '' comment '话题',
  last_use_time int not null default 0 comment '最新的一次被使用的时间',
  use_count int not null default 0 comment '使用次数，后台也可以随意修改，从大到小排',
  PRIMARY KEY (id),
  index last_use_time (last_use_time),
  index use_count(use_count)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='话题表,上传短视频用'
html;
Db::query($sql);



echo "创建<br>\n";

