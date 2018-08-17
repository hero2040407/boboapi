<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE ds_label (
  id int(11) unsigned  NOT NULL AUTO_INCREMENT,
  ds_id   int          NOT NULL DEFAULT 0  COMMENT '大赛id',
  label   varchar(255) not null default '' comment '大赛才艺标签，例如 跆拳道',
  PRIMARY KEY (id),
  index ds_id(ds_id),
  index label(label)
) ENGINE=innodb  DEFAULT CHARSET=utf8 comment="大赛才艺标签表"

html;
Db::query($sql);


echo "创建<br>\n";

