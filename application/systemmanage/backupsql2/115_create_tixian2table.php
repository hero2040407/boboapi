<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_tixian_apply (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  bean int NOT NULL DEFAULT '0' COMMENT '体现波豆值',
  cny decimal(11,2) NOT NULL DEFAULT '0' COMMENT '人民币',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '申请时间',
  datestr int NOT NULL DEFAULT '0' COMMENT '申请日期',
  is_process tinyint not null default 0 comment '0未处理，1已处理打款，2退回',
  PRIMARY KEY (id),
  KEY uid (uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='提现申请表'
html;
Db::query($sql);

$sql=<<<html
CREATE TABLE bb_tixian_log (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  bean int NOT NULL DEFAULT '0' COMMENT '体现波豆值',
  cny decimal(11,2) NOT NULL DEFAULT '0' COMMENT '人民币',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '打款时间',
  datestr int NOT NULL DEFAULT '0' COMMENT '打款日期',
  apply_id int not null default 0 comment '申请id',
  PRIMARY KEY (id),
  KEY uid (uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='提现日志表'
html;
Db::query($sql);

echo "创建<br>\n";
