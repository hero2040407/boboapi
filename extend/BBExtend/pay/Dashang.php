<?php
/**
 * 用户中心，与购买有关的类
 * 
 * User: 谢烨
 */
namespace BBExtend\pay;
use think\Db;
// use app\push\controller\Pushmanager;
// use app\record\controller\Recordmanager;
// use think\Request;

class Dashang 
{
    /**
     * 价格列表由程序定义
     * 
     * 改了原先一个bug，当是vip时，但已过时，应该用当前时间来加。
     * @return number[][]|string[][]
     */
    public static function price()
    {
        return array(
            ["type" => 1,"price"=>5 ],
            ["type" => 2,"price"=>10 ],
            ["type" => 3,"price"=>50 ],
            ["type" => 4,"price"=>100 ],
            ["type" => 5,"price"=>500 ],
            ["type" => 6,"price"=>1000 ],
            
        );
    }
    
    
}