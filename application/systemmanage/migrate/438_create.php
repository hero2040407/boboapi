<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
CREATE TABLE bb_user_face (
  id int(11) unsigned NOT NULL AUTO_INCREMENT comment '主键,规则id，越大越优先' ,
  uid  int not null default 0 comment '用户id',
  face_id int(11) NOT NULL DEFAULT '0' COMMENT '贴图id',
  PRIMARY KEY (id),
  index uid(uid),
  index face_id(face_id),
  unique uid_face(uid,face_id)
) ENGINE=innodb DEFAULT CHARSET=utf8 COMMENT='用户人脸贴图下载表'
html;
Db::query($sql);



echo "创建<br>\n";

