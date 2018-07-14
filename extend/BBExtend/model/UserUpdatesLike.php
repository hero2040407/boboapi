<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;

use BBExtend\Sys;
use BBExtend\DbSelect;

/**
 * 
 * 
 * 
 */
class UserUpdatesLike extends Model 
{
    protected $table = 'bb_users_updates_like_log';
//     protected $primaryKey="uid";
    
    public $timestamps = false;
    public static $err='';
    
  

   

}
