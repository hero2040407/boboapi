<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_like (
  id int(11) unsigned  NOT NULL AUTO_INCREMENT,
  register_log_id   int  NOT NULL DEFAULT 0  COMMENT '对应ds_register_log表的主键id',
  self_uid     int not null default 0 comment '投票人uid',
  target_uid   int not null default 0 comment '目标uid',
  race_id      int not null default 0 comment '大赛id，对应ds_race表的主键id',
  count        int not null default 1 comment '票数，一般是1',
  create_time  int not null default 0 comment '创建时间，即投票时间',
  PRIMARY KEY (id),
  index race_id(race_id),
  index self_uid(self_uid),
  index target_uid(target_uid),
  index register_log_id(register_log_id)
) ENGINE=innodb  DEFAULT CHARSET=utf8 comment="大赛分享投票日志表"

html;
Db::query($sql);


echo "创建<br>\n";

