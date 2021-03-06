<?php
namespace BBExtend\video;



use BBExtend\fix\MessageType;
use BBExtend\common\Str;
use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 大赛状态。
 * 
 * 
 * | 影响因素类别        | 说明  |

 * 
 * 
 * @author xieye
 *
 */
class RaceStatus
{
    public $race=null;      
    
    
    /**
     * 大赛报名状态。
     * @param unknown $uid
     * @param unknown $ds_id
     * @return number[]|string[]|number[]|number[][]|string[][]
     */
    public static function get_status_v5($uid,$ds_id)
    {
        // 先查主办方。
        // 查手机绑定。
        // 查大赛未开始前。
        //下面是查表。
        $time = time();
        $race = \BBExtend\model\Race::find( $ds_id );
        if (!$race) {
            return ['code' =>0,'message' =>'大赛不存在' ];
        }
        $master_arr=[];
        if ( $race->uid >0 ) {
            $master_arr []= $race->uid;
        }
        $db = Sys::get_container_dbreadonly();
        $sql = "select  uid from ds_sponsor where ds_id =?";
        
        $temp = $db->fetchCol($sql,[ $ds_id ]);
        foreach ($temp as $v) {
            $master_arr[]= $v;
        }
        // xieye,先做其他状态判断。然后最后做手机号绑定判断。！！！
        $user = \BBExtend\model\User::find($uid);
        if (!$user) {
            return ['code' =>0,'message' =>'用户不存在' ];
        }
        $money_fen = intval( $race->money * 100 );
        
        // 谢烨，先单独做一个大赛报名前时间的判断。
        if ( $time < $race->register_start_time ) {
            return ['code'=>1,'data' => ['status'=>1,'describe'=>'1','is_count_down' =>1, 'money_fen'=>$money_fen, ]  ]; //
        }
        
        // 如果已经参加。
        $sql="select * from ds_register_log where zong_ds_id=? and uid=? order by id desc limit 1";
        $result = $db->fetchRow($sql,[ $ds_id, $uid ]);
        // 分几种情况。
        if ( $result ) {
            if ($result['has_pay']) {
                // 已参加,未上传
                
                if ((!$result['pic_id_list']) &&  (!$result['record_url'])  ) {
                    return ['code'=>1,'data' => ['status'=>7,'describe'=>'','is_count_down' =>0,'money_fen'=>$money_fen,  ]  ]; //
                }
                // 下面3种情况都是，已上传，已参加，的各种情况，最好是5 ，全部完成。
                    if ( $result['upload_checked']==2 ) {
                        return ['code'=>1,'data' => ['status'=>8,'describe'=>'','is_count_down' =>0,'money_fen'=>$money_fen,  ]  ]; //
                    }
                    
                    // 
                    if ( $result['upload_checked']==0 ) {
                        return ['code'=>1,'data' => ['status'=>9,'describe'=>'','is_count_down' =>0,'money_fen'=>$money_fen,  ]  ]; //
                    }
                    // 无论是否必传，只有这种情况，才是真正的已参加。status=5
                    if ( $result['upload_checked']==1 ) {
                      //return ['code'=>1,'data' => ['status'=>8,'describe'=>'','is_count_down' =>0,'money_fen'=>$money_fen,  ]  ]; //
                    //}
                // 已参加。
                         return ['code'=>1,'data' => ['status'=>5,'describe'=>'','is_count_down' =>0,'money_fen'=>$money_fen,  ]  ]; //
                    }
                
                
            }else { // 继续支付
                
                if ( $result['has_upload'] ) {//继续支付
                    return ['code'=>1,'data' => ['status'=>3,'describe'=>'','is_count_down' =>0,'money_fen'=>$money_fen,  ]  ]; //
                }else { // 继续上传。
                    return ['code'=>1,'data' => ['status'=>4,'describe'=>'','is_count_down' =>0,'money_fen'=>$money_fen,  ]  ]; //
                }
                
                
            }
        }else {
            // 如果 未参加。
            
            
            // 如果报名结束
            if ( $time > $race->register_end_time ) {
                return ['code'=>1,'data' => ['status'=>6,'describe'=>'','is_count_down' =>0, 'money_fen'=>$money_fen, ]  ]; //
            }
            // 查该用户是否手机绑定
            if ( !$user->is_bind_phone() ) {
                return ['code'=>1,'data' => ['status'=>-204,'describe'=>'','is_count_down' =>0,'money_fen'=>$money_fen,  ]  ]; //
            }
            
            // 我要报名，终于出现！！
            return ['code'=>1,'data' => ['status'=>2, 'describe'=>'','is_count_down' =>0,'money_fen'=>$money_fen,  ]  ]; //
            
        }
    }
    
    
    
    /**
     * 返回 一句话，返回状态，返回按钮文字，返回颜色。
     * 
     * 注意，不校验uid是否存在。节约数据库查询时间！！
     * 
     * 3 * 2 * 4 * 3 * 2 = 144 组合。 
     * 
     *  status = 1
     *  describe = 你好
     *  button_word = 上传参赛视频。
     *  color = red
     *  
     * 
     * @param unknown $uid
     * @param unknown $ds_id
     */
    public static function get_status($uid,$ds_id)
    {
        // 先查主办方。
        // 查手机绑定。
        // 查大赛未开始前。
        //下面是查表。
        $time = time();
        $race = \BBExtend\model\Race::find( $ds_id );
        if (!$race) {
            return ['code' =>0,'message' =>'大赛不存在' ];
        }
        
        
//         if ($time < $race->register_end_time) {
            
//         }
        
        
        $master_arr=[];
        if ( $race->uid >0 ) {
            $master_arr []= $race->uid;
        }
        
        $db = Sys::get_container_dbreadonly();
        $sql = "select  uid from ds_sponsor where ds_id =?";
        
        $temp = $db->fetchCol($sql,[ $ds_id ]);
        foreach ($temp as $v) {
            $master_arr[]= $v;
        }
        
        
        if ( in_array($uid, $master_arr ) ) {
            //如果是主办方，有，
            if ($time > $race->start_time ) { //大赛已开始，主办方 可以直播
                return ['code'=>1,'data' => ['status'=>3,'describe'=>'1'   ]  ];
            }else {
                return ['code'=>1,'data' => ['status'=>1,'describe'=>'1'  ]  ]; // 不能直播
            }
        }
        
        // xieye,先做其他状态判断。然后最后做手机号绑定判断。！！！
        
        // 谢烨，先单独做一个大赛报名前时间的判断。
        if ( $time < $race->register_start_time ){
            return ['code'=>1,'data' => ['status'=>1,'describe'=>'1'  ]  ]; // 
        }
        
        // 现在是大赛报名中，或者之后的情况。
        $v1='大赛报名中';
        if ($time > $race->register_end_time ) {
            $v1 ='大赛报名结束后';
        }
        
        $v2 = '未报名';
        if ( $race->has_success_register( $uid ) ) {
            $v2='报名成功';
        }
        $temp = '视频未上传';
        $v3 = $race->record_status($uid);
        if ($v3  == 2 ) {
            $temp = '视频上传审核中';
        }elseif ( $v3 == 3 ) {
            $temp = '视频上传审核成功';
        }elseif ( $v3 == 4 ) {
            $temp = '视频上传审核失败';
        }
        $v3=$temp;
        
        //1 成功，2失败，0 未选拔
        $temp='未选拔';
        $v4 = $race->upgrade_status($uid);
        if ($v4==1) {
            $temp='选拔晋级';
        }
        if ($v4==2) {
            $temp='选拔淘汰';
        }
        $v4 = $temp;
        
        if ($race->repeat_status==1) {
            $v5 = '允许重复报名';
        }else {
            $v5 = '不允许重复报名';
        }
        // 构建正则
        $pre = "\|{$v1}\|{$v2}\|{$v3}\|{$v4}\|{$v5}";
        
        $list = self::status_list();
        $word='我要报名';
        foreach ($list as $v) {
            if ( preg_match('/'. $pre .'/', $v) ) {
                $arr = explode('|', $v);
                $new=[];
                foreach ( $arr as $vv ) {
                    $t = trim($vv);
                    if ($t) {
                        $new[]= $t;
                    }
                }
                
                $word= $new[ count($new)  -1 ] ;
                break;
            }
        }
        
        // 谢烨，这里把文字转成码！！
        if ($word=='我要报名') {
            $code=  2;
        }
        if ($word == '上传参赛视频') {
            $code = 4;
        }
        if ($word=='开始报名') {
            $code=  1;
        }
        if ($word=='开始直播') {
            $code=  3;
        }
        if ($word=='已参加') {
            $code=  5;
        }
        if ($word=='已结束') {
            $code=  6;
        }
        
        
        if ( $code==2 ) {
            $bind_help = new \BBExtend\user\BindPhone($uid);
            if (!$bind_help->check()) {
                $temp =  $bind_help->get_result_arr();
                $code= $temp['code']  ; // 未报名
            }
        }
        
        // 谢烨，现在还差一句话。
        $sql="select * from ds_user_log where uid=? and ds_id=? order by id desc limit 1";
        $row = $db->fetchRow($sql,[ $uid, $ds_id ]);
        $describe ='1';
        
        if ( $time > $race->register_end_time ){
          $describe='';
        }
        if ($row) {
            $describe = $row['content'];
        }
        
        
        // 最后的额外字段。
        $is_count_down=0;
        if ($describe=='1') {
            $is_count_down=1;
        }
        
        return ['code'=>1,'data' =>['status' =>$code, 'describe' => $describe,
                'is_count_down' => $is_count_down  ] ];
        // 
    }
    
    
    
    private static function status_list()
    {
       $s="|大赛报名中|未报名|视频未上传|未选拔|允许重复报名        |  我要报名  |
|大赛报名中|未报名|视频未上传|未选拔|不允许重复报名       |  我要报名  |
|大赛报名中|未报名|视频未上传|选拔晋级|允许重复报名       |  -  |
|大赛报名中|未报名|视频未上传|选拔晋级|不允许重复报名      |  -  |
|大赛报名中|未报名|视频未上传|选拔淘汰|允许重复报名       |  -  |
|大赛报名中|未报名|视频未上传|选拔淘汰|不允许重复报名      |  -  |
|大赛报名中|未报名|视频上传审核中|未选拔|允许重复报名      |  -  |
|大赛报名中|未报名|视频上传审核中|未选拔|不允许重复报名     |  -  |
|大赛报名中|未报名|视频上传审核中|选拔晋级|允许重复报名     | -   |
|大赛报名中|未报名|视频上传审核中|选拔晋级|不允许重复报名    |  -  |
|大赛报名中|未报名|视频上传审核中|选拔淘汰|允许重复报名     |  -  |
|大赛报名中|未报名|视频上传审核中|选拔淘汰|不允许重复报名    | -   |
|大赛报名中|未报名|视频上传审核成功|未选拔|允许重复报名     | -   |
|大赛报名中|未报名|视频上传审核成功|未选拔|不允许重复报名    |  -  |
|大赛报名中|未报名|视频上传审核成功|选拔晋级|允许重复报名    |  -  |
|大赛报名中|未报名|视频上传审核成功|选拔晋级|不允许重复报名   | -   |
|大赛报名中|未报名|视频上传审核成功|选拔淘汰|允许重复报名    | -   |
|大赛报名中|未报名|视频上传审核成功|选拔淘汰|不允许重复报名   | -   |
|大赛报名中|未报名|视频上传审核失败|未选拔|允许重复报名     | -   |
|大赛报名中|未报名|视频上传审核失败|未选拔|不允许重复报名    | -   |
|大赛报名中|未报名|视频上传审核失败|选拔晋级|允许重复报名    | -   |
|大赛报名中|未报名|视频上传审核失败|选拔晋级|不允许重复报名   |  -  |
|大赛报名中|未报名|视频上传审核失败|选拔淘汰|允许重复报名    | -   |
|大赛报名中|未报名|视频上传审核失败|选拔淘汰|不允许重复报名   |  -  |
|大赛报名中|报名成功|视频未上传|未选拔|允许重复报名       | 上传参赛视频   |
|大赛报名中|报名成功|视频未上传|未选拔|不允许重复报名      | 上传参赛视频   |
|大赛报名中|报名成功|视频未上传|选拔晋级|允许重复报名      | 上传参赛视频   |
|大赛报名中|报名成功|视频未上传|选拔晋级|不允许重复报名     |  上传参赛视频  |
|大赛报名中|报名成功|视频未上传|选拔淘汰|允许重复报名      |  我要报名  |
|大赛报名中|报名成功|视频未上传|选拔淘汰|不允许重复报名     |  上传参赛视频  |
|大赛报名中|报名成功|视频上传审核中|未选拔|允许重复报名     |  已参加  |
|大赛报名中|报名成功|视频上传审核中|未选拔|不允许重复报名    |  已参加  |
|大赛报名中|报名成功|视频上传审核中|选拔晋级|允许重复报名    |  已参加  |
|大赛报名中|报名成功|视频上传审核中|选拔晋级|不允许重复报名   |  已参加  |
|大赛报名中|报名成功|视频上传审核中|选拔淘汰|允许重复报名    |  我要报名  |
|大赛报名中|报名成功|视频上传审核中|选拔淘汰|不允许重复报名   |  已参加  |
|大赛报名中|报名成功|视频上传审核成功|未选拔|允许重复报名    |  已参加  |
|大赛报名中|报名成功|视频上传审核成功|未选拔|不允许重复报名   |  已参加  |
|大赛报名中|报名成功|视频上传审核成功|选拔晋级|允许重复报名   |  已参加   |
|大赛报名中|报名成功|视频上传审核成功|选拔晋级|不允许重复报名  | 已参加   |
|大赛报名中|报名成功|视频上传审核成功|选拔淘汰|允许重复报名   |  我要报名  |
|大赛报名中|报名成功|视频上传审核成功|选拔淘汰|不允许重复报名  |  已参加  |
|大赛报名中|报名成功|视频上传审核失败|未选拔|允许重复报名    |  上传参赛视频  |
|大赛报名中|报名成功|视频上传审核失败|未选拔|不允许重复报名   |  上传参赛视频  |
|大赛报名中|报名成功|视频上传审核失败|选拔晋级|允许重复报名   |  上传参赛视频  |
|大赛报名中|报名成功|视频上传审核失败|选拔晋级|不允许重复报名  |  上传参赛视频  |
|大赛报名中|报名成功|视频上传审核失败|选拔淘汰|允许重复报名   |   我要报名 |
|大赛报名中|报名成功|视频上传审核失败|选拔淘汰|不允许重复报名  |  上传参赛视频  |
|大赛报名结束后|未报名|视频未上传|未选拔|允许重复报名      | 已结束   |
|大赛报名结束后|未报名|视频未上传|未选拔|不允许重复报名     | 已结束   |
|大赛报名结束后|未报名|视频未上传|选拔晋级|允许重复报名     |   已结束 |
|大赛报名结束后|未报名|视频未上传|选拔晋级|不允许重复报名    |  已结束  |
|大赛报名结束后|未报名|视频未上传|选拔淘汰|允许重复报名     |  已结束  |
|大赛报名结束后|未报名|视频未上传|选拔淘汰|不允许重复报名    |  已结束  |
|大赛报名结束后|未报名|视频上传审核中|未选拔|允许重复报名    |  已结束  |
|大赛报名结束后|未报名|视频上传审核中|未选拔|不允许重复报名   |  已结束  |
|大赛报名结束后|未报名|视频上传审核中|选拔晋级|允许重复报名   | 已结束   |
|大赛报名结束后|未报名|视频上传审核中|选拔晋级|不允许重复报名  | 已结束   |
|大赛报名结束后|未报名|视频上传审核中|选拔淘汰|允许重复报名   |  已结束  |
|大赛报名结束后|未报名|视频上传审核中|选拔淘汰|不允许重复报名  |  已结束  |
|大赛报名结束后|未报名|视频上传审核成功|未选拔|允许重复报名   | 已结束   |
|大赛报名结束后|未报名|视频上传审核成功|未选拔|不允许重复报名  |  已结束  |
|大赛报名结束后|未报名|视频上传审核成功|选拔晋级|允许重复报名  | 已结束   |
|大赛报名结束后|未报名|视频上传审核成功|选拔晋级|不允许重复报名 |  已结束  |
|大赛报名结束后|未报名|视频上传审核成功|选拔淘汰|允许重复报名  | 已结束   |
|大赛报名结束后|未报名|视频上传审核成功|选拔淘汰|不允许重复报名 | 已结束   |
|大赛报名结束后|未报名|视频上传审核失败|未选拔|允许重复报名   |  已结束  |
|大赛报名结束后|未报名|视频上传审核失败|未选拔|不允许重复报名  |  已结束  |
|大赛报名结束后|未报名|视频上传审核失败|选拔晋级|允许重复报名  |   已结束 |
|大赛报名结束后|未报名|视频上传审核失败|选拔晋级|不允许重复报名 |  已结束  |
|大赛报名结束后|未报名|视频上传审核失败|选拔淘汰|允许重复报名  |  已结束  |
|大赛报名结束后|未报名|视频上传审核失败|选拔淘汰|不允许重复报名 |  已结束  |
|大赛报名结束后|报名成功|视频未上传|未选拔|允许重复报名     |  已结束  |
|大赛报名结束后|报名成功|视频未上传|未选拔|不允许重复报名    |  已结束  |
|大赛报名结束后|报名成功|视频未上传|选拔晋级|允许重复报名    |  已结束  |
|大赛报名结束后|报名成功|视频未上传|选拔晋级|不允许重复报名   |  已结束  |
|大赛报名结束后|报名成功|视频未上传|选拔淘汰|允许重复报名    |   已结束 |
|大赛报名结束后|报名成功|视频未上传|选拔淘汰|不允许重复报名   |  已结束  |
|大赛报名结束后|报名成功|视频上传审核中|未选拔|允许重复报名   |  已参加  |
|大赛报名结束后|报名成功|视频上传审核中|未选拔|不允许重复报名  | 已参加   |
|大赛报名结束后|报名成功|视频上传审核中|选拔晋级|允许重复报名  |  已参加  |
|大赛报名结束后|报名成功|视频上传审核中|选拔晋级|不允许重复报名 |  已参加  |
|大赛报名结束后|报名成功|视频上传审核中|选拔淘汰|允许重复报名  |  已参加  |
|大赛报名结束后|报名成功|视频上传审核中|选拔淘汰|不允许重复报名 |  已参加  |
|大赛报名结束后|报名成功|视频上传审核成功|未选拔|允许重复报名  |  已参加  |
|大赛报名结束后|报名成功|视频上传审核成功|未选拔|不允许重复报名 |  已参加  |
|大赛报名结束后|报名成功|视频上传审核成功|选拔晋级|允许重复报名 |  已参加  |
|大赛报名结束后|报名成功|视频上传审核成功|选拔晋级|不允许重复报名  |  已参加  |
|大赛报名结束后|报名成功|视频上传审核成功|选拔淘汰|允许重复报名 |  我要报名  |
|大赛报名结束后|报名成功|视频上传审核成功|选拔淘汰|不允许重复报名  |  已结束  |
|大赛报名结束后|报名成功|视频上传审核失败|未选拔|允许重复报名  |  已结束  |
|大赛报名结束后|报名成功|视频上传审核失败|未选拔|不允许重复报名 |  已结束  |
|大赛报名结束后|报名成功|视频上传审核失败|选拔晋级|允许重复报名 |  已结束  |
|大赛报名结束后|报名成功|视频上传审核失败|选拔晋级|不允许重复报名  | 已结束   |
|大赛报名结束后|报名成功|视频上传审核失败|选拔淘汰|允许重复报名 |  已结束  |
|大赛报名结束后|报名成功|视频上传审核失败|选拔淘汰|不允许重复报名  |  已结束  |"; 
        
       return preg_split('/[\r\n]+/s', $s);
    }
    
    
    
    
    

}