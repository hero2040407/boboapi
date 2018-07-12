<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;






$sql=<<<html
CREATE TABLE bb_advise (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  
  title varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  create_time int not null default 0 comment '创建时间',
  agent_uid int not null default 0 comment '经纪人bobo号，必须有',
  type      int not null default 0 comment '分类，关联 bb_advise_type 表的主键id',
  address varchar(255) not null default '全国' comment '地区',
  end_time  int not null default 0 comment '结束时间，时间戳',
  auth     tinyint not null default 0 comment '0不限，31 vip, 32 签约童星。',
  audition_card_type int not null default 0 comment '0不需试镜卡，大于0对应bb_audition_card_type的主键id',
  is_active tinyint not null default 0 comment '1激活，0未激活未审核',
  money_fen int not null default 0 comment '0不需费用，大于0表示需要报名费用，单位是分，不是元',
  PRIMARY KEY (id),
 index agent_uid(agent_uid),
  index create_time (create_time),
  index type (type),
  index auth(auth),
  index audition_card_type(audition_card_type)
  
) ENGINE=innodb  DEFAULT CHARSET=utf8 COMMENT='试镜卡类型表'



html;
Db::query($sql);






echo "创建<br>\n";

