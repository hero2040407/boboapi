<?php
namespace BBExtend\user\check;

/**
 * 
 * 
 * User: 谢烨
 */



/**
 * 
 * 
 * @author Administrator
 *
 */
abstract  class Record
{
    protected  $record_arr;
    public $message;
    const xiuchang = 1;
    const yaoyue = 2;
    const renzheng = 3;
    
    protected $act_id;
    protected $uid;
    protected $type;//1,2,3
    protected $result; // 这是布尔，
    protected $record_id; //
    
    public function __construct($record_arr)
    {
        $this->record_arr = $record_arr;
        $this->uid = intval( $record_arr['uid']);
        $this->record_id = intval( $record_arr['id']);
        
        $this->act_id = intval( $record_arr['activity_id']); // 要点：假如record不是邀约，则act_id为null或0
        $this->result=true; // 假设审核结果正确。
        $this->message='';  
    }
    
    public function get_result_json()
    {
        if ($this->result) {
           return ['code'=>1, 'message'=>'ok'];
        }else {
           return ['code'=>0,'message'=> $this->get_message()]; 
        }
    }
    
    /**
     * 
     * 
     * @return Record 
     */
    public static function get_check_instance($record_arr)
    {
        switch ($record_arr['type']) {//1 秀场，2邀约，3认证
            case self::xiuchang:
                return new XiuChang($record_arr);
                break;
            case self::yaoyue:
                return new YaoYue($record_arr);
                break;
            case self::renzheng:
                return new RenZheng($record_arr);
                break;
            default:
                return null;
        }
    }
    
    abstract function success();
    abstract function fail();
    
    public function get_message()
    {
        return $this->message; 
    }

}