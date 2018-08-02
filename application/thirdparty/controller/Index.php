<?php

namespace app\thirdparty\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;
use BBExtend\service\Sms;
use think\Session;

/**
 * 234
 * @author xieye
 *
 */
class Index
{
    

    /**
     * 新版首页
     * @param number $uid
     * @return 
     */
    public function login($phone, $check_code)
    {
        $sms = new Sms( $phone );
        $result = $sms->check( $check_code );
        
        $db = Sys::get_container_db();
        
        if (isset( $result['code'] ) && $result['code'] == 1) {
            
            
            // 谢烨，现在我先在代理表中查。
            
            $sql="select * from backstage_admin
                   where phone=? and level=1 
";
            $row =$db->fetchRow($sql,[ $phone ]);
            if ($row) {
                if ($row['is_valid']==0) {
                    return ['code'=>0,'message' =>'账号禁止登录' ];
                }
                Session::set('thirdparty_is_login', '1');
                Session::set('thirdparty_account_id', $row['id']);
                
                
                return ['code' =>1, 'data' =>[ 'account' =>$row['account'],'password' => $row['pwd_original']  ] ];
                
            }else {
                // 现在我要创建代理账号。
                $pwd = \BBExtend\common\Pwd::create_full_pass();
                $db2 = Sys::get_container_db_eloquent();
                $pwd = mt_rand( 100000, 999999 );
                $id =  $db2::table('backstage_admin')->insertGetId([
                        'account'=>$phone,
                        'pwd' =>md5( $pwd ),
                        'realname' => '',
                        'phone' =>$phone,
                        'level' =>1,
                        'is_valid' =>1,
                        'create_time' =>time(),
                        'parent' => 0,
                        'pwd_original' => $pwd,
                ]);
                
                Session::set('thirdparty_is_login', '1');
                Session::set('thirdparty_account_id', $id);
                
                return ['code' =>1, 'data' =>[ 'account' =>$phone ,'password' =>$pwd  ] ];
            }
            
            
            
        } else {
            return $result;
        }
       
    }
    
    
    
    public function add($register_start_time=0, $register_end_time=0,
            $start_time=0, $end_time=0,
            $title,$banner,
            $summary='',$detail='',
            $min_age=0, $max_age=0,
            $reward='', $money=0)
    {
        
        $id=0;
        $is_login =  Session::get('thirdparty_is_login');
        if ($is_login && $is_login == 1 ) {
            $proxy_id = Session::get('thirdparty_account_id');
            
            $race  = new \BBExtend\model\Race();
            
            $race->proxy_id = $proxy_id;
            $race->register_start_time = intval( $register_start_time );
            $race->register_end_time = intval( $register_end_time );
            $race->start_time = intval( $start_time );
            $race->end_time = intval( $end_time );
            
            
            $race->uid = 0;
            $race->is_active =0;
            
            $race->title = strval( $title );
            $race->banner_bignew = strval( $banner );
            $race->summary = strval( $summary );
            $race->detail = strval( $detail );
            $race->min_age = intval( $min_age );
            $race->max_age = intval( $max_age );
            $race->reward = strval( $reward );
            $race->online_type = 2;
            $race->money = floatval( $money );
            
            
            
            $race->save();
            
            return ['code' =>1,'data' =>[ 'race_id' =>$race->id  ] ];
        }else {
            
            return ['code' =>0, 'message' => '请您先登录'  ];
        }
        
        
        
        
        
    }
    
    
    

}





