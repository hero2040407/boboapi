<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;




$sql=<<<html
CREATE TABLE ds_offline_register_log (
  id int(11) NOT NULL AUTO_INCREMENT,
  ds_id int(11) NOT NULL DEFAULT '0' COMMENT '大赛id',
  uid int(11) NOT NULL DEFAULT '0' COMMENT '报名者uid',
  create_time int(11) NOT NULL DEFAULT '0' COMMENT '报名时间',
  has_join tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否参加过，即上传过视频',
  money decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '人民币费用，单位元',
  phone char(11) NOT NULL DEFAULT '' COMMENT '手机号',
  sex tinyint(4) NOT NULL DEFAULT '1' COMMENT '1男 ，0女',
  birthday varchar(255) NOT NULL DEFAULT '' COMMENT '生日类似 2017-01',
  name varchar(255) NOT NULL DEFAULT '' COMMENT '真实姓名',
  has_pay tinyint(4) NOT NULL DEFAULT '0' COMMENT '1付过钱或大赛无需付钱，0未付钱',
  has_dangan tinyint(4) NOT NULL DEFAULT '0' COMMENT '1填过档案或大赛无需填档案，0未填档案',
  pic varchar(255) NOT NULL DEFAULT '' COMMENT '个人照片',
  zong_ds_id int(11) NOT NULL DEFAULT '0' COMMENT '总赛事id，一定是level为1的大赛id，对于ds_race表',
  has_changed tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否改变过赛区，0正常，1改变过',
  area1_name varchar(255) NOT NULL DEFAULT '' COMMENT '省名称',
  area2_name varchar(255) NOT NULL DEFAULT '' COMMENT '市名称',
  remark varchar(255) NOT NULL DEFAULT '',
  height int(11) NOT NULL DEFAULT '0' COMMENT '用户身高，单位厘米。',
  weight int(11) NOT NULL DEFAULT '0' COMMENT '用户体重，单位公斤。',
  is_web_baoming tinyint(4) NOT NULL DEFAULT '0' COMMENT '1表示vue的web页面报名，0表示app内报名',
  PRIMARY KEY (id),
  UNIQUE KEY xieyeindex1 (ds_id,uid),
  KEY uid (uid),
  KEY ds_id (ds_id),
  KEY phone (phone),
  KEY zong_ds_id (zong_ds_id)
) ENGINE=innodb  DEFAULT CHARSET=utf8mb4 COMMENT='大赛线下报名日志表'

html;
Db::query($sql);



echo "创建<br>\n";

