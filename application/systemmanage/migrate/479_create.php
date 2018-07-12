<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
CREATE TABLE bb_msg_user_config_v3 (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  type int(11) NOT NULL DEFAULT '0' COMMENT '具体类型的id',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  value tinyint(4) NOT NULL DEFAULT '1' COMMENT '1接受推送，0不接受。',
  PRIMARY KEY (id),
  KEY uid (uid),
  KEY type (type)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='新版_系统消息推送设置'

 
html;
Db::query($sql);













echo "创建<br>\n";

