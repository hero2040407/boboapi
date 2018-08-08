<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE lt_roulette (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  title varchar(255) not null default '' comment '奖项名称',
  pic   varchar(255) not null default '' comment '图片',
  lt_type  tinyint  not null default 0 comment '1波币，2表情包, 3谢谢参与，4再来一次，5实物奖品',
  bonus_id int not null default 0 comment '对应lt_bonus表主键，实物奖品id',     
  PRIMARY KEY (id),
  index bonus_id(bonus_id),
  index lt_type(lt_type)      
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='幸运转盘奖项表，只能8行数据'   
html;
Db::query($sql);


$sql=<<<html
CREATE TABLE lt_bonus (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  title  varchar(255) not null default '' comment '奖项名称',
  pic   varchar(255) not null default '' comment '图片',
  style  varchar(255) not null default '' comment '样式，逗号分隔的字符串',
  size  varchar(255) not null default '' comment '规格，逗号分隔的字符串',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='所有的转盘实物奖项'
html;
Db::query($sql);

$sql=<<<html
CREATE TABLE lt_draw_log (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid  int not null default 0 comment '用户id',
  lt_type tinyint not null default 0 comment '对应lt_roulette表的lt_type字段。',
  bonus_id  int not null default 0 comment '对应lt_bonus表主键，实物奖品id',
  craete_time int not null  default 0 comment '抽奖时间戳',
  datestr char(8) not null default '' comment '抽奖日期，类似20170801',
  PRIMARY KEY (id),
  index uid(uid),
  index lt_type(lt_type),
  index bonus_id(bonus_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='幸运转盘抽奖记录表'
html;
Db::query($sql);

$sql=<<<html
CREATE TABLE lt_user_owner (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid  int not null default 0 comment '用户id',
  lt_type  tinyint  not null default 0 comment '对应lt_roulette表的lt_type字段。',
  bonus_id int not null default 0 comment '对应lt_bonus表主键，实物奖品id',     
  craete_time int not null  default 0 comment '获得时间',
  is_use tinyint not null default 0 comment '0未使用，1已使用',
  PRIMARY KEY (id),
  index uid(uid),
  index bonus_id(bonus_id),
  index lt_type(lt_type)      
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户持有兑换券表'
html;
Db::query($sql);

$sql=<<<html
CREATE TABLE lt_exchange_log (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid  int not null default 0 comment '用户id',
  lt_type  tinyint  not null default 0 comment '对应lt_roulette表的lt_type字段。',
  bonus_id int not null default 0 comment '对应lt_bonus表主键，实物奖品id',
  style  varchar(255) not null default '' comment '样式，逗号分隔的字符串',
  size  varchar(255) not null default '' comment '规格，逗号分隔的字符串',
  craete_time int not null  default 0 comment '获得时间',
  datestr char(8) not null default '' comment '兑换日期，类似20170801',
  admin_name varchar(255) not null default '' comment '后台操作者名称',
  PRIMARY KEY (id),
   index uid(uid),
  index bonus_id(bonus_id),
  index lt_type(lt_type)      
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户兑换记录表'
html;
Db::query($sql);

$sql=<<<html
CREATE TABLE lt_user_task (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  uid  int not null default 0 comment '用户id',
  type  int  not null default 0 comment '从2到11，很长，参见tower.im文档',
  craete_time int not null  default 0 comment '获得时间',
  datestr char(8) not null default '' comment '任务日期，类似20170801',
  game_count int not null default 0 comment '当日免费抽奖次数',
  PRIMARY KEY (id),
  index datestr(datestr),
   index uid(uid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户免费抽奖任务表'
html;
Db::query($sql);







echo "创建<br>\n";
