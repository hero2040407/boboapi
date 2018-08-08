<?php

/**
 * 创建
 * 
 * xieye
 */

use think\Db;


$arr = [
    "jifeng","anzhi","zhangshang"
    ,"youyi"
    ,"mumayi"

    ,"3ganzhuo"
    ,"baidu"
    ,"anbei"
    ,"tengxun"
    ,"leshangdian"

    ,"vivo"
    ,"leshi"
    ,"aliyun"
    ,"zhihuiyun"
    ,"oppo"

    ,"sougou"
    ,"yingyongjie"
    ,"360"
    ,"xiaomi"
    ,"meizu"

    ,"ios"
];

foreach ($arr as $v) {
$sql=<<<html
alter TABLE bb_tongji_huizong_register
add first_{$v} varchar(255)  not null default '' comment '{$v}渠道下载第一次使用次数'    
html;
Db::query($sql);
}




echo "创建<br>\n";
