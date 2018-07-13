<?php

namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 试镜卡列表
 * 
 * @author xieye
 *
 */
class AuditionCard
{
    

    /**
     * 
     * @param number $uid
     * @return 
     */
    public function index($uid=10000,$startid=0, $length=10)
    {
       $db = Sys::get_container_dbreadonly();
       $startid=intval($startid);
       $length = intval($length);
       
       $sql="select id from bb_audition_card_type 
where bigtype<3 or  exists(
  select 1 from bb_advise 
   where bb_advise.audition_card_type = bb_audition_card_type.id
)
order by  id asc
limit {$startid},{$length}
";
       $result = $db->fetchCol($sql);
       $new=[];
       foreach ($result as $id) {
           $card = \BBExtend\model\AuditionCardType::find($id);
           $new[]= $card->get_info();
       }
        
       return [
               'code'=>1,
               'data'=>[
                       'list' =>$new,
                       'is_bottom' =>( count( $new )== $length )?0:1,
               ]
       ];
    }
    
    
    

}


