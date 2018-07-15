<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;

use BBExtend\message\Message;

/**
 * 
 * 
 * User: 谢烨
 */
class Advise extends Model 
{
    protected $table = 'bb_advise';
    
    public $timestamps = false;
    
    
    
    
    private static function success_by_third($paytype){
        if ($paytype=='ali') {
            return 'success';
        }
        return '<xml><return_code><![CDATA[SUCCESS]]></return_code></xml>';
        
    }
    
    /**
     * 支付通告成功。
     */
    public static function pay_process($out_trade_no, $paytype, $transaction_id, $total_fee)
    {
        //注意，这里查的是临时表
        
        $prepare = BaomingOrderPrepare::where( 'serial', $out_trade_no )->first();
        
        if (!$prepare) {
            exit();
        }
        
        //要点：查重复，如果已经处理过，则直接返回成功
        if ($prepare->is_success == 1) {
            return self::success_by_third($paytype);
        }
        
        //否则，应该把订单表中置为成功！
        $prepare->is_success=1;
        $prepare->third_name= $paytype;
        $prepare->third_serial= $transaction_id;
        $prepare->price_fen = $total_fee;
        $prepare->update_time = time();
        
        $prepare->save();
        
        $order = new BaomingOrder();
        $order->uid = $prepare->uid;
        $order->ds_id = $prepare->ds_id;
        $order->serial = $prepare->serial;
        $order->is_success = 1;
        $order->create_time = time();
        $order->third_name = $prepare->third_name;
        $order->third_serial = $prepare->third_serial;
        $order->newtype = $prepare->newtype;
        $order->price_fen = $prepare->price_fen;
        $order->json_parameter = $prepare->json_parameter;
        $order->save();
        
        $uid = $prepare->uid;
        $advise_id = $prepare->ds_id;
        $advise = \BBExtend\model\Advise::find($advise_id);
        
        
        // xieye，现在要绑定一张试镜卡。
        $db = Sys::get_container_db();
        
      //  用乐观锁死循环，确保用户得到一张卡片。
        while (true) {
        $sql="select * from bb_audition_card 
where status=4 and uid=0 
and type_id =?

";
        $card_row = $db->fetchRow($sql, $advise->audition_card_type );
        
        if(!$card_row){
            exit;
        }
        
        $version_old = $card_row['lock_version'];
        $version_new = $version_old+1;
        
        $where = "id = ". $card_row['id'] . "  and lock_version={$version_old}";
        
          $rows_affected = $db->update('bb_audition_card', ['uid' =>$uid,
                'lock_version' => $version_new,
                'status' =>5,
                'bind_time'=>time(),
                
                 ], $where);
            if ($rows_affected) {
              break;
            }
        }
        
        $db->insert("bb_audition_card_bind_log", [
                'uid' =>$uid,
                'card_id' => $card_row['id'],
                'serial' => $card_row['serial'],
                'create_time' =>time(),
        ]);
        
        
        // 现在，插入到报名表当中。
        $json = $prepare->json_parameter;
        $json_arr = json_decode($json,1);
        $db->insert("bb_advise_join", [
                'advise_id' => $advise_id,
                'uid' => $prepare->uid,
                'status' => 1,
                'role_id' => $json_arr['role_id'],
                'create_time' => time(),
                'audition_card_id' =>$card_row['id'],
        ]);
        
        
        $content="您已成功报名". $advise->title;

        
        Message::get_instance()
          ->set_title('系统消息')
          ->set_time(time() )
          ->add_content(Message::simple()->content( $content ))
          ->set_type(190)
          ->set_newtype(1)
          ->set_uid($prepare->uid)
          ->send();
      
        
          return self::success_by_third($paytype);
        
    }
    
    
    public function check_card_count(){
        if ($this->audition_card_type==0) {
            return 100000;
        }
        
        $db = Sys::get_container_dbreadonly();
        $sql="select count(*) from bb_audition_card where status=4 and type_id=?";
        $count = $db->fetchOne($sql, $this->audition_card_type);
        return $count;
    }
    
    
    public function has_join($uid)
    {
        $db = Sys::get_container_dbreadonly();
        $sql="select * from bb_advise_join where uid=? and advise_id=?";
        $row = $db->fetchRow($sql,[ $uid, $this->id ]);
        if ($row) {
            return $row;
        }else {
            return false;
        }
    }
    
    
    /**
     * 得到通告详情。
     * 
     * (end_time - time())/( 24 * 3600 )
     * 最后取整。
     * ceil() 
     * 
     * @return string[]|NULL[]
     */
    public function get_index_info()
    {
        
        $time_info = ( $this->end_time - time() )/( 24 * 3600 );
        $time_info = ceil( $time_info );
        $time_info = "剩余{$time_info}天截止报名";
        
        $db = Sys::get_container_dbreadonly();
        $sql="select name from  bb_advise_type where id=?";
        $type_name = $db->fetchOne($sql,[ $this->type ]);
        
        $sql="select count(*) from bb_advise_join 
              where  advise_id=? ";
        $join_count = $db->fetchOne($sql, [ $this->id ]);
       // audition_card_type
       
        $audition_card_name='';
        $audition_card_type=0;
        
        $card_name='';
        if ($this->audition_card_type) {
            $sql="select name,bigtype from  bb_audition_card_type where id=?";
            $row = $db->fetchRow($sql,[ $this->audition_card_type ]);
            
            //$card_name = $db->fetchOne($sql,[ $this->audition_card_type ]);
            $card_name=strval($row['name']  );
            $audition_card_type=intval( $row['bigtype'] );
            
        }
        
        
        return [
           'address' =>$this->address,
                'time'=> $time_info,
                'reward' => '报酬面议',
                'title' =>$this->title,
                'id'=>$this->id,
                'pic'  => $this->pic,
                'pic2' => $this->pic2,
                'is_recommend'=>$this->is_recommend,
                'type_name' =>$type_name,
                'join_count' =>$join_count,
                'auth' =>$this->auth,
                'card_name' =>$card_name,
                'card_type'   => $audition_card_type,
        ];
    }
    
    /**
     * 通告详情。
     */
    public function detail_info()
    {
        $info = $this->get_index_info();
        // 查经纪人。
      //  
        $db = Sys::get_container_dbreadonly();
        $agent_uid = $this->agent_uid;
        $user = \BBExtend\model\User::find( $agent_uid );
        
        $info['agent'] =[
                'uid' => $user->id,
                'pic' =>$user->get_userpic(),
                'nickname' => $user->get_nickname(),
                'phone'  =>$user->get_agent_phone(),
        ];
        
        
        $info['h5_info'] = $this->h5_info;
        $info['character_list'] =[];
        $sql="select id from bb_advise_role where advise_id = ?";
        $result= $db->fetchCol($sql,[ $this->id ]);
        foreach ($result as $role_id){
            $role = AdviseRole::find( $role_id );
            $info['character_list'][] = $role->index_info();
        }
             
        // 谢烨，添加是否可以上传视频。
        
        
         return $info;        
    }
    
    
    
    
    
    
}
