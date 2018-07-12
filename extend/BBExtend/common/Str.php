<?php
namespace BBExtend\common;
/**
 * 通用字符串函数
 * 
 * 
 * @author 谢烨
 */
class Str
{
    /**
     * sql语句使用的like过滤
     * @param unknown $s
     */
    public static function like($s){
        $s = preg_replace ( '/\s+/', '', stripslashes ( $s ) );
        $s = preg_replace ( '/[^\x{4e00}-\x{9fa5}0-9a-zA-Z]/u', '', $s );
        return $s;
    }
    
    // 全是汉字
    public static function is_all_chinese($name){
        return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $name) ;
    }
    
    public static function is_valid_ip($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }
    
    public static function is_valid_phone($phone) {
        return preg_match('/^[\d]{11}$/', $phone) ;
    }
    
    public static function is_valid_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function is_valid_birthday_month($birthday) {
        return preg_match('/^[\d]{4}-[\d]{2}$/', $birthday) ;
    }
    
    public static function is_valid_birthday_day($birthday) {
        return preg_match('/^[\d]{4}-[\d]{2}-[\d]{2}$/', $birthday) ;
    }
    
    /**
     * 封装mbstring中的长度函数
     * 
     * @param string $s 输入字符串
     * 
     * @return integer unicode长度
     */
    public static function strlen($s)
    {
         $length =   mb_strlen($s, 'UTF-8');
        return intval($length);
    }
    
    /**
     * 封装mbstring中的长度函数
     * 
     * @param string $s 输入字符串
     * 
     * @return integer unicode长度
     */
    public static function strlen_real($s)
    {
        $temp = self::g2u($s);
        //这里，要解除html
        $temp = html_entity_decode($temp,ENT_QUOTES,'UTF-8');
       
         $length =   mb_strlen($temp, 'UTF-8');
//            $temp = self::u2g($temp);
        return $length;
    }
    
    
    /**
     * 封装mbstring中的长度函数，把gbk转成utf8
     * 
     * @param string $s 待转换字符串
     * 
     * @return string
     */
    public static function g2u($s)
    {
        return mb_convert_encoding($s, 'UTF-8', 'GBK');
    }

    /**
     * 封装mbstring中的长度函数，把utf8转成gbk
     * 
     * @param string $s 待转换字符串
     * 
     * @return string
     */
    public static function u2g($s)
    {
        return mb_convert_encoding($s, 'GBK', 'UTF-8');
    }
    
    
    /**
     * 更好的分切函数，为解决下列我问题出现
     * 
$s ='';
$aa = explode(',', $s);
var_dump($aa);

     * 上述代码中，会出现一个长度为1的数组，而我们期望出现数组的长度为0
     * 
     * 
     * @param unknown $string
     * @param string $delimit
     * @return string[]
     */
    public static function explode($string, $delimit=',')
    {
        $result = array();
        $temp = explode($delimit, $string);
        if ($temp) {
           foreach ($temp as $v) {
               $t = trim($v);
               if ($t) {
                   $result[] = $t;
               }
           }
        }
        return $result;
        
    }
    
    
    /**
     * 封装mbstring中的截取字符串函数，一个汉字算一个字符
     *
     * @param string  $str    待截取的字符串
     * @param integer $start  起始位置，从0开始
     * @param integer $length 截取长度
     *
     * @return string
     */
    public static function substr($str, $start, $length)
    {
        return mb_substr($str, $start, $length, 'UTF-8');
    }
    
    
    public static function str_cut($sourcestr,$cutlength,$symbol='..')
    {
        $returnstr='';
        $i=0;
        $n=0;
    
        //$sourcestr = mb_convert_encoding($sourcestr,"UTF-8","GBK");
        $str_length=strlen($sourcestr);    //字符串的字节数
        while (($n<$cutlength) and ($i<=$str_length)) {
            $temp_str=substr($sourcestr,$i,1);
            $ascnum=Ord($temp_str); //得到字符串中第$i位字符的ascii码
            if ($ascnum>=224) //如果ASCII位高与224，
            {
                $returnstr=$returnstr.substr($sourcestr,$i,3); //根据UTF-8编码规范，将3个连续的字符计为单个字符
                $i=$i+3; //实际Byte计为3
                $n++; //字串长度计1
            } elseif ($ascnum>=192)//如果ASCII位高与192，
            {
                $returnstr=$returnstr.substr($sourcestr,$i,2); //根据UTF-8编码规范，将2个连续的字符计为单个字符
                $i=$i+2; //实际Byte计为2
                $n++; //字串长度计1
            } elseif ($ascnum>=65 && $ascnum<=90) //如果是大写字母，
            {
                $returnstr=$returnstr.substr($sourcestr,$i,1);
                $i=$i+1; //实际的Byte数仍计1个
                $n++; //但考虑整体美观，大写字母计成一个高位字符
            } else //其他情况下，包括小写字母和半角标点符号，
            {
                $returnstr=$returnstr.substr($sourcestr,$i,1);
                $i=$i+1;    //实际的Byte数计1个
                $n=$n+0.5;    //小写字母和半角标点等与半个高位字符宽…
            }
        }
        if ($returnstr == $sourcestr )
        {
            return $returnstr;
        }else {
            return $returnstr . $symbol;
        }
    }
    
    
  
}//end class

