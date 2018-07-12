<?php
namespace BBExtend\user\achievement;

/**
 * 
 * 直播更新成就
 * User: 谢烨
 */

class Zhibo extends Ach implements UpdateInterface
{
    public function get_event()
    {
        return 'zhibo';
    }
    
    /**
     * 
     * @param int $param 直播时长
     * @see \BBExtend\user\achievement\Ach::update()
     */
    public function update_ach($param)
    {
        if ($param==0) {
            return;
        }
        $param=intval($param);
        
        $old_ach = $this->ach->zhibo;
        
        //不管怎样，需要更新汇总表
        $ach_obj = $this->ach;
        $summary_obj = $this->ach_summary;
        $summary_obj->zhibo += $param;
        $summary_obj->save();
        
        // 根据现在的level，来判断是否给成就表更新数据。
        $new_ach = self::jisuan($summary_obj->zhibo);
        
        if ($old_ach!= $new_ach) {
            $ach_obj->zhibo = $new_ach;
            $ach_obj->save();
            $this->old = $old_ach;
            $this->new = $new_ach;
            return true;
        }
        
    }
    
    public static function jisuan($zhibo)
    {
           if ($zhibo <10* 3600) {
                $result = 0;
            }elseif ($zhibo < 100* 3600) {
                $result = 1;
            }elseif ($zhibo < 500* 3600) {
                $result = 2;
            }else {
                $result = 3;
            }
        return $result;
    }
    
    public function get_bonus_value($new_level) {
        switch ($new_level) {
            case 1:
                return 40;
            case 2:
                return 80;
            case 3:
                return 150;
        }
        return 0;
    }
    
}