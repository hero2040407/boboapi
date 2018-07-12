<?php
namespace BBExtend\user;

use BBExtend\Sys;
use BBExtend\common\Json;
use BBExtend\fix\TableType;
use BBExtend\BBUser;
use BBExtend\Currency;
use BBExtend\DbSelect;

/**
 * 微信公众号相关类
 *
 */
class Weixin
{
    
    const appid = 'wx190ef9ba551856b0';
    const secret = '55a4e4aa42e36a3691ee242c967ffd5f';
    
    private $uid=0;
    private  function __construct() {
//         $this->uid = intval($uid);
    }
    
    public static function getInstance()
    {
        return new self();
    }
    
    // 微信网页登录确认，有接口调用
    public static function code_check($code)
    {
        $code=strval($code);
        if (!$code) {
            return ['code'=>0,'message' =>'code is null' ];
        }
        
        
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='. self::appid .
        '&secret=' . self::secret . '&code='. $code.'&grant_type=authorization_code' ;
        $result = file_get_contents ( $url );
        //  $redis->set ( $key, $result ); // 保存在redis里的是一个json字符串，包括token和失效时间。
        $json = json_decode ( $result, true );
        //   $redis->setTimeout ( $key, $json ['expires_in'] );
        
        //   $json = json_decode ( $result, true );
        if ($json && isset( $json['access_token'] ) && isset( $json['unionid'] )   ){
            return ['code'=>1,'data' =>$json ] ;
            
            
            
        }
        return ['code'=>0,'message'=>'解析错误'];
    }
    
    
    
    /**
     * 
     *  微信手机合并登录
     本接口一定返回一个uid，
     本接口不校验openid,unionid，phone，全部由外部确保真实。
     
     如果存在微信和手机，但号码不同，提示用户改变手机
     
     如果微信和手机相同，则 返回正确的uid
     
     
     如果微信有了，但手机号没有，则把手机号绑定到账号上。
     
     如果手机有了，但微信没有，则把微信绑到手机上。
     
     
     如果都没有，则创建帐户，并把这两者绑到一起。
     
     * 
     * @param unknown $openid
     * @param unknown $unionid
     * @param unknown $phone
     * @param string $access_token
     * @return 
     */
    public static function weixin_phone_login($openid, $unionid, $phone,$access_token='' )
    {
        if ( (!$openid) || (!$unionid) || (!$phone)   ) {
            return ['code'=>0];
        }
        $db = Sys::get_container_db();
        
        $weixin_uid = $phone_uid = 0;
        
            //  $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_users where unionid=? 
and exists(
 select 1 from bb_users_platform
  where bb_users_platform.uid = bb_users.uid
    and bb_users_platform.type =1

)

limit 1";
        $row = $db->fetchRow($sql,[ $unionid ]);
        if ($row ) {
            $weixin_uid = $row['uid'];
        }
        if (!$weixin_uid ) {
            $sql="select * from bb_users_platform where platform_id=? and type =? ";
            $row = $db->fetchRow( $sql, [
                    md5( $openid ),
                    TableType::bb_users__login_type_weixin
            ] ) ;
            if ( $row ) {
                $weixin_uid = $row['uid'];
            }
        }
        
        
        $sql="select * from bb_users_platform where platform_id=? and type =? ";
        $row = $db->fetchRow( $sql, [
                md5( $phone ),
                TableType::bb_users__login_type_shouji,
        ] ) ;
        if ( $row ) {
            $phone_uid = $row['uid'];
        }
        
        if ($weixin_uid && $phone_uid && $weixin_uid != $phone_uid   ){
            
            return ['code'=>0, 'message' =>'该手机号已绑定其他微信号无法关联当前微信号，请前往APP解绑账号或尝试更换手机号码。详情请咨询怪兽BoBo客服。' ];
        }
        
        if ($weixin_uid && $phone_uid && $weixin_uid == $phone_uid   ){
            
            $result = self::get_userinfo($phone_uid);
            return ['code'=>1, 'data' =>$result  ];
        }
        
        if ( $weixin_uid  && (!$phone_uid)  ) {
           //把手机绑定到uid
           
            // 先查该uid下有没有 手机
            $sql="select count(*)  from bb_users_platform where uid=? and type =?";
            $temp = $db->fetchOne($sql,[ $weixin_uid, TableType::bb_users__login_type_shouji ]);
            if ($temp ) {
               
                return ['code'=>0, 'message' =>'您的微信账号已存在，且绑定了手机，而此手机与您填写的手机不一致'  ];
            }
            
            
            $db->insert('bb_users_platform', ['platform_id'=>md5( $phone ),
                    'original' => $phone,
                    'type'=>TableType::bb_users__login_type_shouji ,
                    'uid'=>$weixin_uid,
            ]);
            $result = self::get_userinfo($weixin_uid);
            return ['code'=>1, 'data' =>$result  ];
        }
        
        
        if ( $phone_uid  && (!$weixin_uid )  ) {
            //把微信号绑定到uid
            
            // 先查该uid下有没有 手机
            $sql="select count(*)  from bb_users_platform where uid=? and type =?";
            $temp = $db->fetchOne($sql,[ $phone_uid, TableType::bb_users__login_type_weixin ]);
            if ($temp ) {
                
                return ['code'=>0, 'message' =>'您的手机账号已存在，且绑定了微信号，而此微信号与您现在登录的微信号不一致'  ];
            }
            
            
            $db->insert('bb_users_platform', ['platform_id'=>md5( $openid ),
                    'original' => $openid,
                    'type'=>TableType::bb_users__login_type_weixin ,
                    'uid'=>$phone_uid,
            ]);
            
            // 这里有一个差别，请一定要把unionid填写到user表
            $db->update('bb_users',  ['unionid' => $unionid  ],  'uid='.$phone_uid );
            $result = self::get_userinfo($phone_uid);
            return ['code'=>1, 'data' =>$result  ];
        }
        
        if ( (!$weixin_uid) && ( !$phone_uid )  ) {
            
            // 先按微信注册。
            $pic ='';
            $nickname='小朋友';
            $json = self::get_pic_by_scope_userinfo($openid, $access_token);
            if ($json) {
                $pic = $json['pic'];
                $nickname= $json['nickname'];
            }
            
            $UserDB = BBUser::registered( $nickname, '',  
                    TableType::bb_users__login_type_weixin  , '', $pic,
                    $openid, $unionid );
            
            $uid = $UserDB['uid'];
            $UserDB['currency'] = Currency::get_currency( $uid );
            
            $obj = \app\user\model\UserModel::getinstance( $uid );
            
            // xieye，除了钱表，还有经验表，必须注册时添加 2016 10 24
            \BBExtend\Level::get_user_exp( $uid );
            
            // 谢烨，新功能。新用户注册，自动关注10000号用户，只在正式服。
            if (Sys::is_product_server( )) {
                $help = \BBExtend\user\Focus::getinstance( $uid );
                $help->focus_guy( 10000 );
            }
            $bonus = BBUser::regis_additional( $uid ); // 注册有一个额外流程，必须走。
            
            // 再绑定手机号
            $db->insert('bb_users_platform', ['platform_id'=>md5( $phone ),
                    'original' => $phone,
                    'type'=>TableType::bb_users__login_type_shouji ,
                    'uid'=>$uid,
            ]);
            $result = self::get_userinfo($weixin_uid);
            return ['code'=>1, 'data' =>$result  ];
            
        }
        
        
        // xieye ,如果都有
        
    }
    
    //便利函数。
    private static function get_userinfo($uid)
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_users where uid=?";
        $row=$db->fetchRow($sql,[ $uid ]);
        return [ 'uid' => $uid, 'token' => $row['userlogin_token']  ];
    }
    
    
    
    
    // 网页方式，根据openid获取个人信息。
    public static function get_pic_by_scope_userinfo($openid, $access_token){
        
        if ( $openid && $access_token ) {
          $url= "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
          $response = \Requests::get($url);
        
          $json = Json::decode($response->body);
          if ( isset( $json['errcode'] ) ) {
              return null;
          }
          return [
                  'nickname' => $json['nickname'],
                  'pic'      => $json['headimgurl'],
          ];
        }
        return null;
    }
    
    
    
    
    /**
     * 每次接受到推送，由这里处理
     * 
     * 微信服务器在五秒内收不到响应会断掉连接，并且重新发起请求，总共重试三次。
     * 
     * @param unknown $arr
     */
    public function event($arr) {
        if (array_key_exists('FromUserName', $arr) &&
            array_key_exists('MsgType', $arr) &&
            array_key_exists('Event', $arr) &&
            $arr["MsgType"] == "event" &&
            in_array($arr['Event'], ['unsubscribe', 'subscribe'])   
        ) {
           switch ($arr['Event']) {
               case 'subscribe':
                   $this->focus_push($arr['FromUserName'], $arr['CreateTime']);
                   break;
               case 'unsubscribe':
                   $this->unfocus_push($arr['FromUserName'], $arr['CreateTime']);
                   break;
           }
        }
    }
    
    /**
     * 用户关注我们的公众号
     * @param unknown $openid
     * @param unknown $time
     * 
     * 
     */
    private function focus_push($openid,$time)
    {
        $db = Sys::get_container_db();
        //不能这样，得先查询是否存在。
        $sql="select count(*) from bb_user_weixin_id where gz_openid=?";
        $count = $db->fetchOne($sql, $openid);
        if ($count) {// 已存在，就修改一下。
            $db->update("bb_user_weixin_id", [
                "is_active" => 1,
                "update_time" => $time,
            ],  "gz_openid='{$openid}'");
        }
        else {
            $unionid = $this->get_unionid($openid);
            $db->insert("bb_user_weixin_id", [
                "gz_openid" => $openid,
                "create_time" => $time,
                "is_active" =>1,
                "unionid" => $unionid,
            ]);
            
        }
        
        $node_service = Sys::get_container_node();
        $url = \BBExtend\common\BBConfig::get_touchuan_url();
        $data= ['data'=>[ 'message' =>'关注公众号成功！邀请点评通知已开启'  ],'uid'=>10000,'type'=>6];
        $result = $node_service->http_Request($url,$data,'GET');
        
        
    }
    
    
    public function get_unionid($openid)
    {
       // $openid = 'oFERUwGQ6Zp99bxmwWEIDXRlbyQ0';
        $token = Sys::get_wx_gongzhong_token();
        $url= "https://api.weixin.qq.com/cgi-bin/user/info?".
                "access_token={$token}&openid={$openid}&lang=zh_CN";
        $response = \Requests::get($url);
        
        $json = Json::decode($response->body);
        return $json['unionid'];
    }
    
    
    /**
     * 用户取消关注我们的公众号
     * @param unknown $openid
     * @param unknown $time
     */
    private function unfocus_push($openid,$time)
    {
        $db = Sys::get_container_db();
        $db->update("bb_user_weixin_id", [
            "is_active" => 0,
            "update_time" => $time,
        ],  "gz_openid='{$openid}'");
    
    }
    
    
}