<?php
/**
 * 短视频星推官模块
 */

namespace app\record\controller;



class Starmakerinfo
{
    /**
     * 星推官单独详情页
     */
    public function index($starmaker_id=0)
    {
        $s=<<<html
<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=1.0"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name='apple-mobile-web-app-status-bar-style' content='black'>
    <meta name='format-detection' content='telephone=no'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{$title}-详情</title>
    <link rel="stylesheet" type="text/css" href="{$css}">
    <script type="text/javascript" src="/share/js/jquery.min.js"></script>
    <script type="text/javascript" src="/share/js/Adaptive.js"></script>
</head>

<body>
<div class="main" id="main">
{$detail}
</div>
</body>
</html>

html;
echo $s;
    }
    
      
}