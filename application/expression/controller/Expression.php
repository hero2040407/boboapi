<?php
namespace app\expression\controller;
use think\Db;
use app\currency\controller\Currencymanager;

class Expression 
{
    //api start
    //购买表情包
    public function buy()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $package_id = input('?param.package_id')?(int)input('param.package_id'):0;
        $PackageDB = $this->get_expression_package($package_id);
        if (!$PackageDB)
        {
            return  ['message'=>'非法的表情包ID,请确认您的表情包ID正常','code'=>0];
        }
        $BuyDB = Db::table('bb_expression_buy')->where(['uid'=>$uid,'package_id'=>$package_id])->find();
        if ($BuyDB)
        {
            return  ['message'=>'你已经购买过该表情了','code'=>0];
        }
        $CurDB = \BBExtend\Currency::add_currency($uid,1,-$PackageDB['currency_num'],'购买表情');
        if ($CurDB)
        {
            Db::table('bb_expression_buy')->insert(['uid'=>$uid,'package_id'=>$package_id,'time'=>time()]);
            return ['data'=>$CurDB,'message'=>'购买成功','code'=>1];
        }else
        {
            return  ['message'=>'您的余额不足，是否前往充值','code'=>\BBExtend\fix\Err::code_yuebuzu];
        }
    }
    //得到表情包列表
    public function get_list()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $startid = input('?param.startid')?input('param.startid'):0;
        $length = input('?param.length')?input('param.length'):20;
        $package_DB = Db::table('bb_expression_package')->where('is_show',1)->order("id",'desc')
           ->limit($startid,$length)->select();
        
        $ServerName = \BBExtend\common\BBConfig::get_server_url();
        $Data = array();
        foreach ($package_DB as $package)
        {
            $package['id'] = (int)$package['id'];
            $package['currency_type'] = (int)$package['currency_type'];
            $package['currency_num'] = (int)$package['currency_num'];
            $package['heat_level'] = (int)$package['heat_level'];
            $BuyDB = Db::table('bb_expression_buy')->where(['uid'=>$uid,'package_id'=>$package['id']])->find();
            $package['url'] = $ServerName.$package['url'];
        //    $package['pic'] = $ServerName.$package['pic'];
            
            $package['pic'] =   \BBExtend\common\PicPrefixUrl::add_pic_prefix_https( $package['pic']);
            
            if ($BuyDB)
            {
                $package['is_buy'] = true;
            }else
            {
                $package['is_buy'] = false;
            }
            if ($package['currency_num']==0 && $package['is_limit']==0 ) {
                $package['is_buy'] = true;
            }
            array_push($Data,$package);
        }
        if (count($package_DB) == $length)
        {
            return ['data'=>$Data,'is_bottom'=>0,'code'=>1];
        }
        return ['data'=>$Data,'is_bottom'=>1,'code'=>1];
    }
    //得到表情地址
    public function get()
    {
        $id = input('?param.id')?(int)input('param.id'):0;
        $expDB = Db::table('bb_expression')->where('id',$id)->find();
        header('Location:'.\BBExtend\common\BBConfig::get_server_url().$expDB['url']);
        exit();
    }
    //私有函数
    private function get_expression_package($package_id)
    {
        $DB = Db::table('bb_expression_package')->where('id',$package_id)->find();
        $DB['id'] = (int)$DB['id'];
        $DB['currency_type'] = (int)$DB['currency_type'];
        $DB['currency_num'] = (int)$DB['currency_num'];
        $DB['heat_level'] = (int)$DB['heat_level'];
        return $DB;
    }
}