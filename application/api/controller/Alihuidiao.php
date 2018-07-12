<?php
namespace app\api\controller;

use BBExtend\Sys;
use think\Controller;

class Alihuidiao extends  Controller
{
    const input_domain='http://upload.guaishoubobo.com/';// 输入的网址
    
    /**
     * 返回最新的安卓版本
     */
    public function index()
    {
        
   //     Sys::debugxieye("alihuidiao:ok");
        
        $str = $GLOBALS['HTTP_RAW_POST_DATA'];
        
        $json = preg_replace('#^.+?(\{.+\})[^}]*$#s', '$1', $str);
        $json = json_decode($json,1);
//         Sys::debugxieye(11);
        if ( isset($json['RunId']) && isset($json['Type']  ) && isset($json['State']   )     ) {
            $this->process($json);
            
            
            header('HTTP/1.1 204 No Content');
            return;
        }
        
        
    }
    
    
    /**
     * http://upload.guaishoubobo.com/mov_mp4_convert/8055938/qSplzn33gXk1.qt
     * 改成
     * 
     * @param unknown $json
     */
    
    private function process($json)
    {
        $runid = $json['RunId'];
//         Sys::debugxieye('input22:');
        if ( $json['Type']=='Report' && $json['State']=='Success' ) {
//             Sys::debugxieye('input33:');
            $input_file= self::input_domain . $json['MediaWorkflowExecution']['Input']['InputFile']['Object'];
            $target = "http://convert.guaishoubobo.com/mov_mp4_convert/{$runid}.mp4";
            $db = Sys::get_container_db();
            $sql="select * from bb_record where video_path =?";
          //  $video_path = 
            $result = $db->fetchRow($sql,[ $input_file ]);
//             Sys::debugxieye('input44:');
//             Sys::debugxieye('input:'.$input_file);
            
            // 谢烨注意：这是自动转码特别慢，先有record表，然后转码的情况。
            if ($result) {
//                 Sys::debugxieye('input55:');
                
                $sql="update bb_record set video_path=?,transcoding_complete=1  where id=?";
                $db->query( $sql,[ $target,  $result['id'] ] );
               
//                 Sys::debugxieye('out:'. $target );
                
              //  $db->update('bb_record', ['video_path' =>$target ],""  );
                
            }else {
                // 谢烨注意，这是用户操作太慢，先转码，然后最后存record表的情况，此时必须先存暂存表
                $db->insert('bb_aliyun_record',[
                        'create_time' => time(),
                        'video_path' => $input_file,
                        'target_path' => $target,
                ]);
                
                
            }
        }
        
    }
    
   
    public function test(){
        
        $str=<<<js

1952926561392430
q
1952926561392430
q-vod
98F540CE053DA0AD-2-162B2CD2CE4-200000002
3A4C34916D3CEDFD30DADDBAAEF7B014
{"RunId":"fa893b2a36204d75a69b01225d61950b","Name":"Act-Report","Type":"Report","State":"Success","MediaWorkflowExecution":{"MediaWorkflowId":"ef761e30211241f4bcd6ff931f92d71c","Name":"bobo_ios_mov格式转换2018","RunId":"fa893b2a36204d75a69b01225d61950b","MediaId":"ec39a27155b14362a787f8e9fdce725b","Input":{"InputFile":{"Bucket":"bobo-sql","Location":"oss-cn-beijing","Object":"mov_mp4_convert/xx/IMG_42.MOV"}},"State":"Completed","ActivityList":[{"RunId":"fa893b2a36204d75a69b01225d61950b","Name":"activityStart","Type":"Start","JobId":"597655360fb7480ca8f356d711d7a8c8","State":"Success","StartTime":"2018-04-11T03:43:28Z","EndTime":"2018-04-11T03:43:30Z"},{"RunId":"fa893b2a36204d75a69b01225d61950b","Name":"Act-ss-mp4-hd","Type":"Transcode","JobId":"647c6085e93d4190be3870512256a756","TemplateId":"S00000001-200010","State":"Success","StartTime":"2018-04-11T03:43:30Z","EndTime":"2018-04-11T03:43:33Z"},{"RunId":"fa893b2a36204d75a69b01225d61950b","Name":"Act-Report","Type":"Report","State":"Success","StartTime":"2018-04-11T03:43:33Z","EndTime":"2018-04-11T03:43:33Z"}],"CreationTime":"2018-04-11T03:43:28Z","RequestId":"5ACD845707B9954EC039919B"}}
1523418213604
js;
        $json = preg_replace('#^.+?(\{.+\})[^}]*$#s', '$1', $str);
        $json = json_decode($json,1);
        dump( $json);
    }
   
}
