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
class RenZheng extends Record
{
    public function __construct($record_arr) {
        parent::__construct($record_arr);
        $this->type=3;
    }
    
    public function success()
    {
        echo "renzheng_success_" . $this->record_arr['type'] .'_'.$this->record_arr['title'];  
    }
    
    public function fail()
    {
        echo "renzheng_fail_" . $this->record_arr['type'] .'_'.$this->record_arr['title'];
    }
    
}