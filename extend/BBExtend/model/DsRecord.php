<?php
namespace BBExtend\model;
use \Illuminate\Database\Eloquent\Model;
use BBExtend\Sys;
use BBExtend\DbSelect;
/**
 * 
 * 
 * : 谢烨
 */
class DsRecord extends Model 
{
    protected $table = 'ds_record';
    public $timestamps = false;
    
//     public function set_rank($rank)
//     {
        
//     }
    
    public static function like($record_id)
    {
        ///Sys::debugxieye(11);
        
        $db = Sys::get_container_db_eloquent();
        $sql="
select ds_id from ds_record 
where record_id=?
";
        $ds_id = DbSelect::fetchOne($db, $sql,[ $record_id ]);
        
        if ($ds_id) {
           // Sys::debugxieye(112);
            
            $race = Race::find( $ds_id );
            if ($race  ) {
             //   Sys::debugxieye(113);
                
                if ( $race->end_time >  time() ) {
               //     Sys::debugxieye(114);
                    
                    $sql ="update ds_record
                set like_count=like_count +1 where record_id=? ";
                    $db::update($sql,[ $record_id ]);
                    
                }
                
            }
        }
        return false;
    }

}
