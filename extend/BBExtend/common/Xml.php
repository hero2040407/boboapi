<?php

namespace BBExtend\common;

/**
 * 通用xml函数
 * 
 * 
 * @author 谢烨
 */
class Xml {
    //数组转xml
    public static function arrayToXml($arr,$root = 'root')
    {
        $xml = '<'.$root.'>';
        foreach ($arr as $key=>$val)
        {
            if(is_array($val))
            {
                $xml.="<".$key.">". self::arrayToXml($val)."</".$key.">";
            }else{
                $xml.="<".$key.">".$val."</".$key.">";
            }
        }
        $xml.='</'.$root.'>';
        return $xml;
    }
    
    //解析读取xml数据，然后先转成json格式，再转换成数组
    public static function xmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }
    
}

