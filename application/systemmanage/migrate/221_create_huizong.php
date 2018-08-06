<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;



$sql=<<<html
CREATE TABLE bb_tongji_huizong_register (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  datestr int(11) NOT NULL DEFAULT '20160101' COMMENT '统计日期，格式类似20161001，固定8位',
  all_count int not null default 0 comment '总计',
  other_count int not null default 0 comment '其他总计',
  name_jifeng    int not null default 0 comment '机锋网',
  name_anzhi int not null default 0 comment '安智',
  name_zhangshang int not null default 0 comment '掌上应用汇',
  name_youyi int not null default 0 comment '优亿市场',
  name_mumayi int not null default 0 comment '木蚂蚁市场',
  name_3ganzhuo int not null default 0 comment '3G安卓市场',
  name_baidu int not null default 0 comment '百度开发者中心',
  name_anbei int not null default 0 comment '安卓在线/安贝市场',
  name_tengxun int not null default 0 comment '应用宝',
  name_leshangdian int not null default 0 comment '乐商店',
  name_vivo int not null default 0 comment '步步高vivo',
  name_leshi int not null default 0 comment '乐视',
  name_aliyun int not null default 0 comment '阿里云',
  name_zhihuiyun int not null default 0 comment '智汇云应用市场',
  name_oppo int not null default 0 comment 'Oppo NearMe',
  name_sougou int not null default 0 comment '搜狗手机助手',
  name_yingyongjie int not null default 0 comment '应用街',
  name_360 int not null default 0 comment '360',
  name_xiaomi int not null default 0 comment '小米商店',
  name_meizu int not null default 0 comment '魅族应用中心',
  PRIMARY KEY (id),
  KEY datestr (datestr)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='注册统计汇总表'   
html;
Db::query($sql);







echo "创建<br>\n";
