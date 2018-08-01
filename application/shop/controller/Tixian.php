<?php

namespace app\shop\controller;
// use BBExtend\BBShop;
use think\Db;
use think\Controller;
use BBExtend\BBUser;
use BBExtend\Currency;
use BBExtend\Sys;

/**
 * 商城主
 *
 * Created by PhpStorm.
 * User: xieye
 * Date: 2016/8/3
 * Time: 11:42
 */
class Tixian extends Controller {
    
    /**
     * 申请提现：
     * 首先，一个人一天只能申请一次。
     * 然后，申请当时的帐号至少 320波豆，即10元 人民币。
     * 条件满足，但是注意；申请提现，最多，一次只能体现200元人民币，即6400波豆。
     * 申请成功后，波豆数值立刻扣除，从当前帐号。
     *
     *
     * @param unknown $openid            
     * @param unknown $uid            
     * @return number[]|string[]
     */
    public function index($openid, $uid, $unionid) {
        if (empty ( $openid )) {
            return [ 
                'code' => 0,
                'message' => '微信号不正确' 
            ];
        }
        $uid = intval ( $uid );
        $user_arr = BBUser::get_user ( $uid );
        if (! $user_arr) {
            return [ 
                'code' => 0,
                'message' => 'uid不正确' 
            ];
        }
        
        $bind_help = new \BBExtend\user\BindPhone($uid);
        if (!$bind_help->check()) {
            return $bind_help->get_result_arr();
        }
        
        $db = Sys::get_container_db();
        $datestr = date ( "Ymd" );
        $sql = "select count(*)  from bb_tixian_apply where uid = {$uid} and 
                datestr = '{$datestr}'
            ";
        $count = $db->fetchOne ( $sql );
//         if ($count) {
//             return [ 
//                 'code' => 0,
//                 'message' => '您今日已申请过提现，如您已经在微信上关注过"怪兽bobo服务号"，敬请耐心等待' 
//             ];
//         }
        
        // 查该用户当前波豆是否够提现。
        $currency_arr = Currency::get_currency ( $uid );
        $bean = $currency_arr ['gold_bean'];
        $cny = Currency::bean_to_cny ( $bean );
        $min_cny = 10;
        $min_bean = Currency::cny_to_bean ( $min_cny );
        
        if ($cny < $min_cny) {
            return [ 
                'code' => 0,
                'message' => "您的bo豆数量不够，需至少{$min_bean}bo豆方可提现" 
            ];
        }
        // 现在条件全部满足了。
        $max_cny = 200;
        $max_bean = Currency::cny_to_bean ( $max_cny );
        $change_bean = $bean;
        if ($bean > $max_bean) {
            $change_bean = $max_bean;
        }
        $change_cny = Currency::bean_to_cny ( $change_bean );
        
        // xieye ，因为人民币取整了，所以再次计算波豆数。
        $change_bean = Currency::cny_to_bean ( $change_cny );
        
        // 谢烨，现在记录到申请表里。
        $db->insert ( "bb_tixian_apply", [ 
            'uid' => $uid,
            'bean' => $change_bean,
            'cny' => $change_cny,
            'create_time' => time (),
            'datestr' => date ( "Ymd" ),
            'is_process' => 0,
            'openid' => strval ( $openid ),
            'unionid' => strval ( $unionid ) 
        ]
         );
        
        \BBExtend\Currency::add_bean ( $uid, 0 - $change_bean, '提现' );
        
        // $sql ="update bb_currency set gold_bean = gold_bean - {$change_bean} where uid={$uid}";
        // $db->query($sql);
        // }
        
        return [ 
            'code' => 1,
            'data' => [ 
                'bean' => intval ( $change_bean ),
                'cny' => intval ( $change_cny ),
                'message' => "微信搜索关注”怪兽bobo服务号“领取红包" 
            ] 
        ];
    }
    
    public function query($startid = 0, $length = 20, $uid) {
        $uid = intval ( $uid );
        $startid = intval ( $startid );
        $length = intval ( $length );
        $db = Sys::get_container_db();
        $user_arr = BBUser::get_user ( $uid );
        if (! $user_arr) {
            return [ 
                'code' => 0,
                'message' => 'uid不正确' 
            ];
        }
        $sql = "select * from bb_tixian_log where uid = {$uid} order by id desc 
           limit {$startid},{$length}";
        $data = $db->fetchAll ( $sql );
        
        $currency_arr = Currency::get_currency ( $uid );
        $bean = $currency_arr ['gold_bean'];
        $gold = Currency::bean_to_cny ( $bean );
        $is_bottom = count ( $data ) == $length ? 0 : 1;
        
        // 谢烨，最后查一个累积领取多少元
        $sql = "select sum(cny) from bb_tixian_log where uid = {$uid} ";
        $sum_cny = $db->fetchOne ( $sql );
        
        return [ 
            "code" => 1,
            'data' => [ 
                'bean' => intval ( $bean ),
                'gold' => $gold,
                'history' => $data,
                'is_bottom' => $is_bottom,
                'sum_cny' => floatval ( $sum_cny ) 
            ] 
        ];
    }
}