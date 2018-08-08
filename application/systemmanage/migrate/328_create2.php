<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_chat_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  create_time int not null DEFAULT 0 COMMENT '当时时间',
  room_id int NOT NULL DEFAULT 0 COMMENT '房间id',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '讲话人id',
  is_robot tinyint NOT NULL DEFAULT 0 COMMENT '1是机器人，0正常',
  content varchar(800) NOT NULL DEFAULT '' COMMENT '聊天内容',
  PRIMARY KEY (id),
  index room_id(room_id),
  index uid(uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='聊天信息日志表'

html;
Db::query($sql);



echo "创建<br>\n";
