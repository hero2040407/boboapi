<?php

namespace app\api\controller;

use think\Config;
use BBExtend\Sys;
use think\Controller;
use think\Db;

/**
 * 
 * 阿里云上传接口
 *
 */

define('UP_RACE_VEDIO',1);//大赛视频
define('UP_RACE_PIC',2);//大赛封面
define('UP_ACTIVITY_VEDIO',3);//活动视频
define('UP_ACTIVITY_PIC',4);//活动视频封面
define('UP_BRAND_PIC',5);//活动视频封面


class Oss extends Controller
{
    public function aliyun_callback(){
        $id = input('?param.id')?input('param.id'):'';
        $filename = input('?param.filename')?input('param.filename'):'';
        $filetype = input('?param.filetype')?input('param.filetype'):'';

        header('HTTP/1.0 200 OK');
        header('Content-Type: application/json');

        if($filename == '') return ['code'=>1, 'message'=>'测试访问成功!id为'.$id];

        return ['code'=>1, 'filepath'=>'http://upload.guaishoubobo.com/'.$filename,'filetype'=>$filetype,'success'=>'true'];
    }



    public function oss_upload() {
        $m_id = input('?param.id')?input('param.id'):'';
        $uid = input('?param.uid')?input('param.uid'):'10000';
        if($uid == 'null')exit('参数错误!');
        $filetype = input('?param.filetype')?input('param.filetype'):'';
        $foldertype = input('?param.foldertype')?input('param.foldertype'):'';
        $filetypefolder = 'upload';
        $id= 'LTAIhwakuNS9SwJW';
        $key= 'lPQs2TfrGkxqLcSDPWs4l1qZL4yoHq';
        $host = 'http://bobo-sql.oss-cn-beijing.aliyuncs.com';
        $callbackUrl = $_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["HTTP_HOST"].'/api/oss/aliyun_callback';
        $callback_param = array('callbackUrl'=>$callbackUrl,
                'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&id='.$m_id.'&filetype='.$filetype.'&foldertype='.$foldertype.'&uid='.$uid,
                'callbackBodyType'=>"application/x-www-form-urlencoded");
        $callback_string = json_encode($callback_param);
        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 30; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
        $end = $now + $expire;
        $expiration = $this->gmt_iso8601($end);

        if($filetype=='mov'){
            $filetypefolder = 'mov_mp4_convert';
        }else{
            switch ($foldertype){
                case UP_RACE_VEDIO:
                    $filetypefolder = 'race';
                    break;
                case UP_RACE_PIC:
                    $filetypefolder = 'race';
                    break;
                case UP_ACTIVITY_VEDIO:
                    $filetypefolder = 'activity';
                    break;
                case UP_ACTIVITY_PIC:
                    $filetypefolder = 'activity';
                    break;
                case UP_BRAND_PIC:
                    $filetypefolder = 'brand';
                    break;
            }
        }

        if(strpos($_SERVER["HTTP_HOST"],'test') === false){
            $dir = $filetypefolder.'/'.$uid.'/';
        }else{
            $dir = 'cs'.$filetypefolder.'/'.$uid.'/';
        }

        //最大文件大小.用户可以自己设置
        $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
        $conditions[] = $condition;

        //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
        $start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
        $conditions[] = $start;

        $arr = array('expiration'=>$expiration,'conditions'=>$conditions);

        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = array();
        $response['accessid'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        $response['callback'] = $base64_callback_body;
        $response['dir'] = $dir;
        echo json_encode($response);
        exit;
    }


    public function oss_upload_nocallback(){
        $uid = input('?param.uid')?input('param.uid'):'10000';
        $filetype = input('?param.filetype')?input('param.filetype'):'';
        $filetypefolder = 'upload';
        $id= 'LTAIhwakuNS9SwJW';
        $key= 'lPQs2TfrGkxqLcSDPWs4l1qZL4yoHq';
        $host = 'http://bobo-sql.oss-cn-beijing.aliyuncs.com';

        $now = time();
        $expire = 30; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
        $end = $now + $expire;
        $expiration = $this->gmt_iso8601($end);

        switch ($filetype){
            case 1:
                $filetypefolder = 'race';
                break;
            case 2:
                $filetypefolder = 'activity';
                break;
        }

        if(strpos($_SERVER["HTTP_HOST"],'test') === false){
            $dir = $filetypefolder.'/'.$uid.'/';
        }else{
            $dir = 'cs'.$filetypefolder.'/'.$uid.'/';
        }

        //最大文件大小.用户可以自己设置
        $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
        $conditions[] = $condition;

        //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
        $start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
        $conditions[] = $start;


        $arr = array('expiration'=>$expiration,'conditions'=>$conditions);
        //echo json_encode($arr);
        //return;
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = array();
        $response['accessid'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        //这个参数是设置用户上传指定的前缀
        $response['dir'] = $dir;
        echo json_encode($response);
    }



    private function gmt_iso8601($time) {
        $dtStr = date("c", $time);
        $mydatetime = new \DateTime($dtStr);
        $expiration = $mydatetime->format(\DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }

    //图文混排提供给后台界面
    public function  ad_html5_edit(){
        $uid = input('?param.uid')?input('param.uid'):'10000';
        $token = input('?param.token')?input('param.token'):'';
        $type = input('?param.type')?input('param.type'):'';

        echo $this->fetch('./photoimg/ad_index.html',['type'=>$type,'uid'=>$uid,'token'=>$token]);
    }
}
