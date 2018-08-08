<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;
$sql=<<<html
CREATE TABLE bb_group (
  id int(11) NOT NULL AUTO_INCREMENT,
  type        tinyint      NOT NULL DEFAULT 0 COMMENT '1微信群，2qq群',
  code        varchar(255) NOT NULL DEFAULT '' COMMENT '建群用的微信号或qq号',
  bb_type     tinyint      not null default 0 comment '1邀约群，2大赛群',
  ds_id       int          NOT NULL DEFAULT 0 COMMENT '仅限大赛群，表示大赛id',
  title       varchar(255) NOT NULL DEFAULT '' COMMENT '群名称',
  pic         varchar(255) NOT NULL DEFAULT '' COMMENT '群图标，单纯显示用',
  qrcode_pic  varchar(255) NOT NULL DEFAULT '' COMMENT '二维码图标，app可以调起加群请求',
  create_time int          not null default 0 comment '创建时间',
  update_time int          not null default 0 comment '最后修改时间',
  PRIMARY KEY (id),
  KEY bb_type (bb_type),
  KEY ds_id (ds_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='聊天群表'
html;
Db::query($sql);







echo "创建<br>\n";
