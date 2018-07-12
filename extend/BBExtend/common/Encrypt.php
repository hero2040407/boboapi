<?php
/**
 * 通用函数
 * 
 * 
 * @author 谢烨
 */
class Public_Encrypt
{
    public static function encrypt($string, $key = '') 
    {
        $key = md5($key);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = substr(md5($keyb.$keya),-4);


        $cryptkey = $keya.md5($keyc.$keyb);
        $key_length = strlen($cryptkey);

        $string = substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);


        $result = '';
        $box = range(0, 255);

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + ord($cryptkey[$i % $key_length])) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }


        return $keyc.str_replace('=', '', base64_encode($result));

    }
    

    public static function decrypt($string, $key = '') {
        $key = md5($key);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = substr(md5($keyb.$keya),-4);


        $cryptkey = $keya.md5($keyc.$keyb);
        $key_length = strlen($cryptkey);

        $string = base64_decode (substr($string, 4));
        $string_length = strlen($string);


        $result = '';
        $box = range(0, 255);

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + ord($cryptkey[$i % $key_length])) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }


        return substr($result,16);

    }
    
    
    
    
  
}//end class

