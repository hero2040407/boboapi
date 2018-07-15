<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
CREATE TABLE bb_audition_card_bind_log (
  id int(11)  NOT NULL AUTO_INCREMENT,
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  uid         int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  serial varchar(255) not null default ''  comment '',
  card_id     int     not null default  0  comment '',
  PRIMARY KEY (id),
  index card_id(card_id),
  index serial(serial),
  index uid(uid)
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='试镜卡绑定表含参加通告'

html;
Db::query($sql);






echo "创建<br>\n";

