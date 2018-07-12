<?php
namespace app\shop\controller;

use think\Db;

/**
 * 
 * 地址接口，但可能没用到
 * 
 * Created by PhpStorm.
 * User: xieye
 * Date: 2016/8/3
 * Time: 11:42
 */
class Address 
{
   
    
    //================================================
    //地址管理模块
    //================================================
    //增加用户地址
    public function add_address()
    {
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $name = input('?param.name')?(string)input('param.name'):'';
        $phone = input('?param.phone')?(string)input('param.phone'):'';//手机号码
        $countries = input('?param.countries')?(string)input('param.countries'):'中国';//国家
        $province = input('?param.province')?(string)input('param.province'):'';//省
        $city = input('?param.city')?(string)input('param.city'):'';//市
        $area = input('?param.area')?(string)input('param.area'):'';//区
        $street = input('?param.street')?(string)input('param.street'):'';//街道地址
        $tel = input('?param.tel')?(string)input('param.tel'):'';//电话
        $is_default = input('?param.is_default')?(int)input('param.is_default'):0;//是否默认地址
        $zip_code = input('?param.zip_code')?(string)input('param.zip_code'):''; //邮编
        if (\app\user\model\Exists::userhExists($uid)!=1)
        {
            return ['message'=>'没有此用户','code'=>0];
        }
        if (!$name)
        {
            return ['message'=>'用户名称不能为空','code'=>0];
        }
        $AddressDB = array();
        if ($phone)
        {
            $AddressDB['phone'] = $phone;
        }
        if ($countries)
        {
            
            $AddressDB['countries'] = $countries;
        }
        if ($province)
        {
            $AddressDB['province'] = $province;
        }
        if ($city)
        {
            $AddressDB['city'] = $city;
        }
        if ($area)
        {
            $AddressDB['area'] = $area;
        }
        if ($street)
        {
            $AddressDB['street'] = $street;
        }
        if ($tel)
        {
            $AddressDB['tel'] = $tel;
        }
        if ($zip_code)
        {
            $AddressDB['zip_code'] = $zip_code;
        }
        if ($is_default)
        {
            $AddressDB['is_default'] = $is_default;
            $DefaultDB_list =  Db::table('bb_address')->where(['uid'=>$uid,'is_default'=>1])->select();
            foreach ($DefaultDB_list as $DefaultDB)
            {
                Db::table('bb_address')->where(['id'=>$DefaultDB['id']])->update(['is_default'=>0]);
            }
        }
        $AddressDB['uid'] = $uid;
        $AddressDB['name'] = $name;
        $AddressDB['time'] = time();
        Db::table('bb_address')->insert($AddressDB);
        $AddressDB['id'] = Db::table('bb_address')->getLastInsID();
        return ['data'=>self::conversion_address($AddressDB),'code'=>1];
    }
    
    
    //删除用户地址
    public function del_address()
    {
        $id = input('?param.id')?(int)input('param.id'):0;
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $AddressDB = Db::table('bb_address')->where(['uid'=>$uid,'id'=>$id])->find();
        if ($AddressDB)
        {
            Db::table('bb_address')->where(['uid'=>$uid,'id'=>$id])->delete();
            return ['message'=>'删除成功','code'=>1];
        }
        return ['message'=>'没有当前的这个ID地址请检查','code'=>0];
    }
    
    
    //修改用户地址
    public function editor_address()
    {
        $id = input('?param.id')?(int)input('param.id'):0;
        $uid = input('?param.uid')?(int)input('param.uid'):0;
        $phone = input('?param.phone')?(string)input('param.phone'):'';//手机号码
        $countries = input('?param.countries')?(string)input('param.countries'):'';//国家
        $province = input('?param.province')?(string)input('param.province'):'';//省
        $city = input('?param.city')?(string)input('param.city'):'';//市
        $area = input('?param.area')?(string)input('param.area'):'';//区
        $street = input('?param.street')?(string)input('param.street'):'';//街道地址
        $tel = input('?param.tel')?(string)input('param.tel'):'';//电话
        $is_default = input('?param.is_default')?(int)input('param.is_default'):-1;//是否默认地址
        $zip_code = input('?param.zip_code')?(string)input('param.zip_code'):''; //邮编
        $AddressDB = Db::table('bb_address')->where(['uid'=>$uid,'id'=>$id])->find();
        if ($AddressDB)
        {
            if ($phone)
            {
                $AddressDB['phone'] = $phone;
            }
            if ($countries)
            {
                $AddressDB['countries'] = $countries;
            }
            if ($province)
            {
                $AddressDB['province'] = $province;
            }
            if ($city)
            {
                $AddressDB['city'] = $city;
            }
            if ($area)
            {
                $AddressDB['area'] = $area;
            }
            if ($street)
            {
                $AddressDB['street'] = $street;
            }
            if ($tel)
            {
                $AddressDB['tel'] = $tel;
            }
            if ($zip_code)
            {
                $AddressDB['zip_code'] = $zip_code;
            }
            if ($is_default)
            {
                $AddressDB['is_default'] = $is_default;
                $DefaultDB_list =  Db::table('bb_address')->where(['uid'=>$uid,'is_default'=>1])->select();
                foreach ($DefaultDB_list as $DefaultDB)
                {
                    Db::table('bb_address')->where(['id'=>$DefaultDB['id']])->update(['is_default'=>0]);
                }
            }
            Db::table('bb_address')->where(['uid'=>$uid,'id'=>$id])->update($AddressDB);
            return ['message'=>'修改成功','code'=>1];
        }
        return ['message'=>'没有此用户请检查UID以及地址id','code'=>0];
    }
    
    
    public function get_address_detail($id=0,$uid=0)
    {
            $AddressDB_list = Db::table('bb_address')->where(['id'=>$id,"uid"=>$uid, ])->select();
          
        return ['data'=>$AddressDB_list,'code'=>1];
    }
    
    
    //得到用户所有地址
    public function get_address_list()
    {
        $uid     =  input('?param.uid')?(int)input('param.uid'):0;
        if(\app\user\model\Exists::userhExists($uid)==1)
        {
            $AddressDB_list = Db::table('bb_address')->where(['uid'=>$uid])->select();
            $DB_List = array();
            foreach ($AddressDB_list as $AddressDB)
            {
                array_push($DB_List,self::conversion_address($AddressDB));
            }
            return ['data'=>$DB_List,'code'=>1];
        }
        return ['message'=>'没有此用户请检查UID','code'=>0];
    }
    
    
    //得到默认地址
    public function get_default_address()
    {
        $uid =  input('?param.uid')?(int)input('param.uid'):0;
        $AddressDB = Db::table('bb_address')->where(['uid'=>$uid,'is_default'=>1])->find();
        if (!$AddressDB)
        {
            $AddressDB = Db::table('bb_address')->where(['uid'=>$uid])->order('time','desc')->find();
        }
        if (!$AddressDB)
        {
            return ['message'=>'该用户没有设置任何地址','code'=>0];
        }
        return ['data'=>self::conversion_address($AddressDB),'code'=>1];
    }
    
    
    //产生订单号
    // 
    private  function get_order_serial($mobile)
    {
        $pre = $mobile=="ios" ? 'BI':'BA';
        
        $orderSn = $pre .date("Ymd") . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) .
            substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
        return $orderSn;
    }
   
    
}