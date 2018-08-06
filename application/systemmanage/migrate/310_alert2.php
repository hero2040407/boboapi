<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
CREATE TABLE bb_record_invite_starmaker_fail (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  user_pic varchar(255) NOT NULL DEFAULT '' COMMENT '用户头像',
  user_nickname varchar(255) NOT NULL DEFAULT '' COMMENT '用户昵称',

  record_id int(11) NOT NULL DEFAULT '0' COMMENT '短视频id',
  status tinyint(4) NOT NULL DEFAULT '1' COMMENT '1发起邀请，2完成邀请即点评,',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '邀请时间，注意这里，保存新的审核时间',
  starmaker_uid int(11) NOT NULL DEFAULT '0' COMMENT '星推官uid',
  answer varchar(1000) NOT NULL DEFAULT '' COMMENT '星推官评论',
  answer_type tinyint(4) NOT NULL DEFAULT '1' COMMENT '1文字，2短视频，3语音',
  answer_time int(11) NOT NULL DEFAULT '0' COMMENT '点评时间',
  zan_count int(11) NOT NULL DEFAULT '0' COMMENT '导师增加的赞数',
  lock_time int(11) NOT NULL DEFAULT '0' COMMENT '上锁时间，正在点评，半小时后失效',
  lock_uid int(11) NOT NULL DEFAULT '0' COMMENT '上锁人uid，正在点评，半小时后失效',
  media_duration int(11) NOT NULL DEFAULT '0' COMMENT '媒体时长，单位秒',
  media_url varchar(255) NOT NULL DEFAULT '' COMMENT '媒体播放地址，是url',
  media_pic varchar(255) NOT NULL DEFAULT '' COMMENT '媒体封面图，适用于视频',
  gold int(11) NOT NULL DEFAULT '0' COMMENT '短视频作者为此付出的波币数，如视频认证失败会改为0',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY record_id (record_id),
  KEY starmaker_uid (starmaker_uid),
  KEY status (status),
  KEY lock_time (lock_time),
  KEY lock_uid (lock_uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='短视频邀请鉴定失败表'
html;
Db::query($sql);

 


echo "创建<br>\n";
