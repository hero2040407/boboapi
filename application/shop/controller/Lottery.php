<?php
namespace app\shop\controller;
use think\Db;
use app\shop\model\ShopGoods;

use think\Controller;
use BBExtend\Sys;

// use app\shop\model\ShopGoods;

/**
 * Created by PhpStorm.
 * User: 谢烨
 * Date: 2016/8/25
 * Time: 11:42
 */

class Lottery  extends Controller
{
    public function index ($uid=0)
    {
        $uid = intval($uid);
        $time2 = time() - 183 * 24 *3600;
       // echo 1;exit;
        $db = Sys::get_container_db();
        // is_use 字段表示是否使用过。
        $sql ="select bonus_id from lt_user_owner 
                 where lt_type=5 and uid = {$uid} and is_use=0  ";
        
        $list = $db->fetchAll($sql);
        $list = (array)$list;
        foreach ($list as $k => $v) {
            $goods_id = $v["bonus_id"];
            $goods = ShopGoods::get($goods_id);
            if (!$goods) {
                return ['code'=>0, 'message'=>'商品不存在'];
            }
            $list[$k]['pic'] = $goods->right_pic();
            $list[$k]['title'] = $goods->getData('title');
           // $list[$k]['pic'] = $goods->right_pic();
            $list[$k]['count'] = 1;
            $list[$k]['price'] = 50;
        }
        
        
        return ["code"=>1, "data" => $list ];
    }
    
    
    
    
}