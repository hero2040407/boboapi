<?php

/**
 * 创建拉黑表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_lahei (
  id int NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid int NOT NULL DEFAULT 0 COMMENT '用户ID',
  target_uid int NOT NULL DEFAULT 0 COMMENT '被拉黑或被举报uid',
  type tinyint NOT NULL DEFAULT 0 COMMENT '1拉黑，2举报',
  small_type tinyint not null default 0 comment '拉黑举报的小类型，程序指定',
  create_time int NOT NULL DEFAULT 0 COMMENT '生成时间',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY target_uid (target_uid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 comment "拉黑举报表"       
html;
Db::query($sql);


echo "创建拉黑表<br>\n";
