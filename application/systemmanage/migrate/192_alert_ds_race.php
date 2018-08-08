<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE ds_register_change_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  zong_ds_id int(11) NOT NULL DEFAULT '0' COMMENT '总大赛id,level=1',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '报名者uid',
  start_ds_id int not null default 0 comment '转变前渠道id',
  end_ds_id int not null default 0 comment '转换后渠道id',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '报名时间',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY zong_ds_id (zong_ds_id),
  KEY start_ds_id (start_ds_id),
  KEY end_ds_id (end_ds_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='大赛报名改变赛区日志表'
html;
Db::query($sql);




echo "创建<br>\n";
