<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;

$sql="
CREATE TABLE bb_subject_movie (
  id int(11) NOT NULL AUTO_INCREMENT,
  subject_id int NOT NULL DEFAULT 0 COMMENT '栏目id',
  room_id varchar(25)  NOT NULL DEFAULT '' COMMENT '视频房间id',
  create_time int not null default 0 comment '创建时间',
  PRIMARY KEY (id),
  unique subject_movie(subject_id,room_id)      
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='栏目视频关联表'        
";
Db::query($sql);



echo "创建<br>\n";
