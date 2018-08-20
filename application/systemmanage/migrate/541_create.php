<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_dangan_config_user_history (
  id int(11) unsigned  NOT NULL AUTO_INCREMENT,
  uid   int  NOT NULL DEFAULT 0  COMMENT 'uid',
  title     varchar(255) not null default '' comment '项目标题',
  content   varchar(255) not null default '' comment '项目答案',
  PRIMARY KEY (id),
  index uid(uid),
  index title(title)
) ENGINE=innodb  DEFAULT CHARSET=utf8 comment="用户填写档案历史信息表"

html;
Db::query($sql);


echo "创建<br>\n";

