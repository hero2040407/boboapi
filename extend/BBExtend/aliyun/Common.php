<?php
namespace BBExtend\aliyun;
require_once realpath( EXTEND_PATH). "/aliyun-openapi-php-sdk/aliyun-php-sdk-core/Config.php";
/**
 * 通用
 * 
 * 
 * @author 谢烨
 */
class Common
{
    const  accessKeyId= 'LTAIdnZssaoNUoGc';
    const  accessSecret= 'QSvRUGKeEOgEPDCfcK7VnQmVuA6bYD';
        //推流地址
    const   ALY_SERVER_PUSH_URL = 'www.yimwing.com';
  
    public static function describeLiveStreamsOnlineList($domain='www.yimwing.com')
    {
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", self::accessKeyId, 
                self::accessSecret );
        $client = new \DefaultAcsClient($iClientProfile);
        //构建 请求
        $request = new \live\Request\V20161101\DescribeLiveStreamsOnlineListRequest();
        $request->setMethod("GET");
        $request->setDomainName( $domain  );
        $response = $client->getAcsResponse($request);
        //该数组就是阿里云返回给我的正在直播的流。
        //每个元素又是一个标准类。
        //         stdClass Object
        //         (
        //                 [PublishTime] => 2017-03-16T05:03:02Z
        //                 [StreamName] => LV3EV9FG-1002442push
        //                 [PublishUrl] => rtmp://www.yimwing.com/bobo/LV3EV9FG-1002442push
        //                 [DomainName] => www.yimwing.com
        //                 [AppName] => bobo
        //                 )
        $arr = $response->OnlineInfo->LiveStreamOnlineInfo;
        $result =[];
        foreach ($arr as $v) {
            $result []= $v->StreamName;
        }
        return $result;
    }
    
    private $client;
    public function __construct(){
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", self::accessKeyId,
                self::accessSecret );
        $this->client = new \DefaultAcsClient($iClientProfile);
        
    }
    
    
    /**
     * 禁止直播播放。
     * 谢烨 20171018
     * @param string $domainName 域名例如 push3.yimwing.com
     * @param string $streamName 流名称，例如 UAK2PQ0U-6700440push
     */
    public  function kill($domainName, $streamName)
    {
        
        $client = $this->client;
        //构建 请求
        $request = new \live\Request\V20161101\ForbidLiveStreamRequest();
        
        $request->setLiveStreamType('publisher');
        $request->setStreamName($streamName);
        
        $request->setMethod("GET");
        $request->setAppName('bobo');
        
        $request->setDomainName( $domainName  );
        $response = $client->getAcsResponse($request);
        return $response;
    }
    
    /**
     * 恢复直播
     * @param unknown $domainName
     * @param unknown $streamName
     * @return unknown|mixed
     */
    public function resume($domainName, $streamName)
    {
        $client = $this->client;
        //构建 请求
        $request = new \live\Request\V20161101\ResumeLiveStreamRequest();
        
        $request->setLiveStreamType('publisher');
        $request->setStreamName($streamName);
        
        $request->setMethod("GET");
        $request->setAppName('bobo');
        
        $request->setDomainName( $domainName  );
        $response = $client->getAcsResponse($request);
        return $response;
    }
    
    
    
}//end class

