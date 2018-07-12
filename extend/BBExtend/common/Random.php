<?php
/**
 * 通用
 * 
 * 
 * @author 谢烨
 */
class Public_Random
{
    public static function uuid() {
        return md5(uniqid(rand(), true));
    
    }
    
    function get_unique()
    {
        $uid = uniqid("", true);
        $uid = $uid.time();
        for ($Index = md5( $uid, true ),
                $String = '0123456789ABCDEFGHIJKLMNOPQRSTUV',//32位必须
                $Data = '',
                $Begin = 0;
                 
                $Begin < 8;
                 
                $Go = ord( $Index[ $Begin ] ),
                $Data .= $String[ ( $Go ^ ord( $Index[ $Begin + 8 ] ) ) - $Go & 0x1F ],
                $Begin++
                );
        return $Data;
    
    }
    
  
}//end class

