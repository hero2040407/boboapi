<?php
/**
 * 用户个人信息
 */

namespace app\user\controller;

use BBExtend\model\User;
use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\model\BrandShop;
use think\Controller;

class Infohtml extends Controller 
{
    //type=1简介，2表示荣誉，3表示课程
    public function index ($type=1,$uid=0)
    {
        $isvalid = BrandShop::isvalid($uid);
        if (!$isvalid) {
            echo 'error';
            return;
        }
        //echo 1;
        $info='';
        $html_info='';
        $title='';
        $obj = BrandShop::getinfo($uid);
        if ($type==1) {
            $info = $obj->info;
            $html_info = $obj->html_info;
            $title='品牌馆介绍';
        }
        if ($type==2) {
            $info = $obj->rongyu;
            $html_info = $obj->html_rongyu;
            $title='荣誉资质';
        }
        if ($type==3) {
            $info = '';
            $html_info = $obj->html_kecheng;
            $title='免费课程';
        }
        //echo 3;
        echo $this->fetch('index',
                [
                        'info' =>$info,
                        'html_info' => $html_info,
                        'title' =>$title,
                ]
        );
      //  echo 2;
    }
    
}


