<?php

namespace app\api\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;

use BBExtend\model\UserUpdates;
use BBExtend\model\UserUpdatesMedia;
use BBExtend\model\UserUpdatesComment;
use BBExtend\model\UserUpdatesLike;



/**
 * 点赞，动态
 * 
 * @author xieye
 *
 */
class UpdatesLike
{
    /**
     * 
     * 
     * 
     * type 1动态，2评论，3回复
     * 
     * @param number $uid
     * @param unknown $id
     * @param number $like
     * @param number $type
     */
    public function add($uid=10000,$id, $like=1, $type=1 )
    {
        if ($type==1) {
            $obj = UserUpdates::find( $id );
            $obj->add_like( $uid );
        }else {
            
            $obj = UserUpdatesComment::find( $id );
            $obj->add_like( $uid );
        }
        
        
    }
    

}


