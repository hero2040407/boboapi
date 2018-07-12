<?php
namespace BBExtend\user\check;
use BBExtend\user\check\Record;
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
class XiuChang extends Record
{
    public function __construct($record_arr) {
        parent::__construct($record_arr);
        $this->type=1;
    }
    
    public function success()
    {
        echo "xiuchang_success_" . $this->record_arr['type'] .'_'.$this->record_arr['title'];  
    }
    
    public function fail()
    {
        echo "xiuchang_fail_" . $this->record_arr['type'] .'_'.$this->record_arr['title'];
    }
    
}