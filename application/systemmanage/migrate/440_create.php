<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
CREATE TABLE backstage_admin (
  id int(11)  NOT NULL AUTO_INCREMENT comment '主键，账号' ,
  account varchar(255) not null default '' comment '账号名',
  pwd     varchar(255) not null default '' comment '账号密码，md5后',
  realname varchar(255) not null default '' comment '真实姓名',
  phone    varchar(255) not null default '' comment '手机号',
  level    tinyint not null default 1 comment '1代理，2渠道',
  is_valid tinyint not null default 0 comment '1有效，0无效',
  create_time int not null default 0 comment '创建时间',
  PRIMARY KEY (id),
  unique account(account)
) ENGINE=innodb DEFAULT CHARSET=utf8 COMMENT='后台代理渠道账号表'

html;
Db::query($sql);


$sql=<<<html
CREATE TABLE backstage_admin_race (
  id int(11)  NOT NULL AUTO_INCREMENT comment '主键，账号' ,
  account_id int not null default 0 comment '账号id',
  race_id    int not null default 0 comment '大赛id',
  field_id   int not null default 0 comment '赛区id，如果是代理账号，则一定为0',
  PRIMARY KEY (id),
  index account_id(account_id),
  index race_id(race_id),
  index field_id(field_id)
) ENGINE=innodb DEFAULT CHARSET=utf8 COMMENT='后台代理渠道账号大赛关联表'

html;
Db::query($sql);




echo "创建<br>\n";

