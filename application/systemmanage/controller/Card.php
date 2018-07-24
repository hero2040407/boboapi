<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;
use BBExtend\Sys;
use BBExtend\DbSelect;
class Card
{
    public function help($serial='')
    {
        $serial = strtoupper($serial);
        
        $db = Sys::get_container_dbreadonly();
        $sql="select type_id from bb_audition_card where serial=?";
        $type_id = $db->fetchOne($sql, $serial);
        if (!$type_id) {
            return ['message' =>'试镜卡号不存在' ];
        }
        $sql="select title from bb_advise where audition_card_type =?";
        $result = $db->fetchCol($sql,[ $type_id ]);
        
        return ['message' =>$result ];
        
    }
    
      
    
   
}











