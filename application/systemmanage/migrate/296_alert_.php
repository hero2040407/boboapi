<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
CREATE TABLE bb_record_invite_starmaker (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  record_id int not null default 0 comment '短视频id',
  status tinyint not null default 1 comment '1发起邀请，2完成邀请即点评,',      
  create_time int not null default 0 comment '邀请时间',
  starmaker_uid int not null default 0 comment '星推官uid',
  answer varchar(1000) not null default '' comment '星推官评论',
  answer_type tinyint not null default 1 comment '1文字，2短视频，3语音',      
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY record_id (record_id),
  KEY starmaker_uid (starmaker_uid),
  KEY status (status)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='短视频邀请鉴定表'
html;
Db::query($sql);




echo "创建<br>\n";
