<?php

/**
 * bb_shop_logistics_trace 表
 * 创建物流单号轨迹表
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_shop_logistics_trace (
   id int(11) NOT NULL AUTO_INCREMENT,
  uid int not null default 0 comment '用户id',
  order_no varchar(255) not null default '' comment '商城订单号',
  logistics varchar(255) not null default '' comment '物流单号',
  company varchar(255) not null default '' comment '物流公司代号',
  create_time int not null default 0 comment '创建时间,就是接收快递鸟推送的时间',
  accept_time    varchar(255) not null default '' comment '轨迹信息1：时间',
  accept_station varchar(500) not null default '' comment '轨迹信息2，事件',
  remote_addr    varchar(255) not null default '' comment '远程ip',
  PRIMARY KEY (id),
  index company(company),
  index logistics(logistics)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 comment "物流订单轨迹表"        
html;


Db::query($sql);



echo "创建物流单号轨迹表<br>\n";

