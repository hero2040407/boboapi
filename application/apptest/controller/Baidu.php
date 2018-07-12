<?php
namespace app\apptest\controller;


use BBExtend\Sys;



class Baidu
{
    public function index()
    {
        
        $content ="禽兽大爷,操你妈";
        $minganci_help = new \BBExtend\model\Minganci();
        $content = $minganci_help->filter_by_asterisk($content);
        echo $content;
        
    }
   
}
