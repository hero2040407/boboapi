<?php

/**
 * 
 *  
 * @author 谢烨
 */
namespace app\systemmanage\controller;
use BBExtend\Sys;
use BBExtend\DbSelect;
use think\Db;
class Qupai 
{
      
     /**
      * //趣拍视频转换到阿里云后更改地址(停用 可以删除)
      */
     public function index()
     {

         header("Content-type: text/html; charset=utf-8");
         $appKey = '20995aca1e3add2';
         $time = time() + 1200;
         $hashValue = md5($time . "-e5bc2344f98d4ebd8dab39d1f1aea6d7");
         $auth_key = $time . "-" . $hashValue;

         $list = Db::table('bb_record_copy1')->where(['is_remove' => 1])->order('id asc')->select();


         foreach ($list as $key => $vo) {
             $str = strstr($vo['video_path'], '/v/');
             $str = strstr($str, '.mp4', TRUE);
             $str = str_replace('/v/', '', $str);

             $res = file_get_contents('http://open.paas.qupaicloud.com/mig/vod/get?appKey=' . $appKey . '&auth_key=' . $auth_key . '&videoId=' . $str);
             $data = json_decode($res, true);

             if ($data['code'] == 200) {
                 $id = 'LTAIdnZssaoNUoGc';
                 $key = 'QSvRUGKeEOgEPDCfcK7VnQmVuA6bYD' . '&';

                 $time = time();

                 $arr['Version'] = '2017-03-21';
                 $arr['AccessKeyId'] = $id;
                 $arr['SignatureMethod'] = 'HMAC-SHA1';
                 $arr['TimeStamp'] = date('Y-m-d', $time) . 'T' . date('H:i:s', $time) . 'Z';
                 $arr['SignatureVersion'] = '1.0';
                 $arr['SignatureNonce'] = $vo['id'] . time() . rand(100000, 999999);
                 $arr['Format'] = 'JSON';
                 $arr['Action'] = 'GetPlayInfo';
                 $arr['VideoId'] = $data['data'];

                 $str1 = "";
                 ksort($arr);
                 foreach ($arr as $k => $v) {
                     if (null != $v && "null" != $v) {
                         $v = urlencode($v);
                         $str1 .= $k . "=" . $v . "&";
                     }
                 }
                 $str2 = substr($str1, 0, strlen($str1) - 1);
                 $Signature = base64_encode(hash_hmac('sha1', 'GET&%2F&' . urlencode($str2), $key, true));
                 $url = 'http://vod.cn-shanghai.aliyuncs.com/?' . $str1 . 'Signature=' . urlencode($Signature);
                 $ch = curl_init();
                 curl_setopt($ch, CURLOPT_URL, $url);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                 curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                 curl_setopt($ch, CURLOPT_HEADER, 0);
                 $res = curl_exec($ch);
                 curl_close($ch);

                 if (strpos($res, '403') === false) {

                     $data = json_decode($res, true);
                     if (isset($data['VideoBase'])) {
                         $savedata['big_pic'] = $data['VideoBase']['CoverURL'];
                         $savedata['thumbnailpath'] = $data['VideoBase']['CoverURL'];
                         $savedata['is_remove'] = 1;
                         foreach ($data['PlayInfoList']['PlayInfo'] as $v) {
                             if ($v['Format'] == 'mp4') {
                                 $savedata['video_path'] = $v['PlayURL'];
                             }
                         }
                         Db::table('bb_record_copy1')->where(['id' => $vo['id']])->update(['is_remove' => 0]);
                         Db::table('bb_record')->where(['id' => $vo['id']])->update($savedata);
                     }
                 }

             }

             echo '成功!';
             exit;
         }

     }
    
   
}