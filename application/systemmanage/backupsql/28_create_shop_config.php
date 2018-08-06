<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_shop_config (
  id int not null AUTO_INCREMENT comment '主键',
  bb_key varchar(255) NOT NULL  default ''  COMMENT '英文键',
  bb_value varchar(2000) not null default '' comment '值',
  info varchar(255) not null default '' comment '中文说明',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '生成时间',
  update_time int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  admin_id int not null default 0 comment '管理员id',
  PRIMARY KEY (id),
  index bb_key(bb_key)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
html;
Db::query($sql);

echo "创建表<br>\n";

