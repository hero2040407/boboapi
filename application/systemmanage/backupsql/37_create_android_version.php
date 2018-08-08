<?php

/**
 * 创建安卓客户端版本表，注意：ios客户端版本表名为 bb_version
 * 
 * xieye
 */

use think\Db;


$sql=<<<html
CREATE TABLE bb_version_android (
  id               int           NOT NULL AUTO_INCREMENT,
  version_name     varchar(255)  not null default '' comment '版本号，如1.1.23',
  version_code     int           not null default 0  comment '整型字段版本号，如2，用于比较',
  is_qiangzhi      tinyint       not null default 0  comment '1强制更新，0不强制',      
  url              varchar(255)  not null default '' comment '下载链接',
  update_content   varchar(1000) not null default '' comment '此版本的更新内容',      
  create_time      int           not null default 0  comment '创建时间',
  update_time      int           not null default 0  comment '更新时间',
  admin_name       varchar(255)  not null default '' comment '管理员名称',
  PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 comment "安卓客户端版本表"        
html;
Db::query($sql);


echo "创建安卓客户端版本表<br>\n";
