<?php
namespace BBExtend\service\pheanstalk;
use Pheanstalk\Pheanstalk;
/**
 * 消息队列。pheanstalk
 * 
 * 本队列的功能：处理导师抢单。
 * 
 * @author 谢烨
 *
 * xieye: 20171016
 * 这是服务端代码，使用方法如下，只有两句话。
 * 
 *  $worker = new \BBExtend\service\pheanstalk\Worker();  
 *  $worker->run();  
 *
 * 如果把程序放后台执行
 *  ( /usr/bin/php /var/www/html/public/index.php /systemmanage/phean/run >> /tmp/phean.log 2>&1 &)
 *
 *
 * 消息队列服务启动停止,注意，停止会自动清空所有消息。
 * service beanstalkd start
 * service beanstalkd stop
 * 
 * 
 * 
 * 代码修改，每发一种消息，
 * 1、message类，添加发送type
 * 
 * 2、WorkerJobPush类，修改工厂方法。
 * 3、type目录下，添加一个文件，做实际的操作。
 * 4、修改代码
 * $client = new \BBExtend\service\pheanstalk\Client();
   $client->add_dianping(
      new \BBExtend\service\pheanstalk\Datadp($uid, $record_id, time()  )
   );
 * 
 * 
 */
class Workerdp
{
    private $pheanstalk;//这是服务
    // 设置队列名称。表示点评抢单
    const queue_name ="dianping";
    
    public function __construct() {
         
        $this->log('starting');
        $this->pheanstalk = new Pheanstalk('127.0.0.1:11300');
    }
    
    /**
     * 永久执行，通过while(true)
     */
    public function run() {
        $this->log('starting to run dp');
    
        while(true) {
            $job = $this->pheanstalk->watch( self::queue_name )->ignore('default')->reserve();
         //   $job_data = unserialize( $job->getData());
    
            $job_data =  $job->getData() ;
            
            // 真正工作的就这句。
            $this->excute($job_data);
            //删除千万不能忘记。
            $this->pheanstalk->delete($job);
    
            $memory = memory_get_usage();
            //这是一个保险措施。也可以去除。
            if($memory > 100000000) {
                $this->log('exiting run due to memory limit');
                exit;
            }
        }
    }
    
    
    private function node($uid, $result,$channel)
    {
        $db = \BBExtend\Sys::get_container_db();
        $db->insert("bb_alitemp", [
                'create_time' => date("Y-m-d H:i:s"),
                'content' => 'message:'.$result['message'],
        ]);
        $redis = \BBExtend\Sys::get_container_redis();
        $redis->lPush($channel, serialize($result) );
        
        $redis->setTimeout($channel, 60 * 60 );
        
//         $node_service = \BBExtend\Sys::get_container_node();
//         $url = \BBExtend\common\BBConfig::get_touchuan_url();
        
//         $temp = json_encode([ 'code'=>$result['code'], 'message'=> $result['message']   ] );
        
//         $data= ['data'=>[ 'code'=>$result['code'], 'message'=> $result['message']   ],'uid'=>$uid,'type'=>7];
//         $result = $node_service->http_Request($url,$data,'GET');
        
        unset($redis);
        
    }
    
    
    private function testlog($uid,$channel)
    {
        $db = \BBExtend\Sys::get_container_db();
        $db->closeConnection();
        $db = \BBExtend\Sys::get_container_db();
        $db->insert("bb_alitemp", [
                'create_time' => date("Y-m-d H:i:s"),
                'uid' => $uid,
                'content'=> "当前时间".date("Y-m-d H:i:s")." 这是一个phean的<b>点评队列</b>的测试。传入的随机数是" .$channel ,
        ]);
    }
    
    /**
     * 抢单处理。
     * 
     * @param Data $data
     */
    private  function excute( $data)
    {
        
        
        $data = urlencode($data);
        $command = "/usr/bin/php /var/www/html/public/index.php /systemmanage/phean_process/dp {$data}";
        
        $out = shell_exec ( $command );
        
        
        
        
//         // 谢烨，这分好几种情况，如果名字已经存在，且此单未点评，则必定运行。
//         // 如果名字不存在，则为抢单，此时，先检查其余短视频，已邀请当前星推官，且未完成的情况，如有，则返回错误
//         $uid =  $data->get_uid();
//         $record_id = $data->get_record_id();
//         $time = $data->get_time();
//         $channel = $data->get_channel();
        
//         if ($uid < 0) {
//             return $this->testlog($uid,$channel);
//         }
        
        
//         $db = \BBExtend\Sys::get_container_db();
//         $db->closeConnection();
//         $db = \BBExtend\Sys::get_container_db();
        
//         $invite =  \BBExtend\model\RecordInviteStarmaker::where(
//                 'record_id', $record_id)->first();
//         if (!$invite) {
//             return $this->node($uid, ['code'=>0, 'message' => 'invite 不存在' ],$channel);
//         }
        
//         if ( $invite->starmaker_uid ) {
//             // 这是指定的情况，此时，如单已完成，返回错误，如单未完成，返回正确。
//             if ( $invite->starmaker_uid == $uid ) {
            
//                 if ($invite->status==1) {
//                     return $this->node($uid, ['code'=>1, 'message' => '' ],$channel);
//                 }else {
//                     return $this->node($uid, ['code'=>0, 'message' => '此单已点评过，不可重复点评' ],$channel);
//                 }
//             }else {
                
//                 return $this->node($uid, ['code'=>0, 'message' => '此单已被他人抢单成功。' ],$channel);
//             }
            
//         }else {
//             // 这是抢单的情况，
//             // 首先，本人是星推官吗,外面已经检查过，
//             // 然后，查当前用户，是否有 其他的status=1 的单子。
            
//             $sql="select count(*)   
//                    from bb_record_invite_starmaker
//                   where starmaker_uid = {$uid}
//                     and status=1
//                     and record_id != {$record_id}
//                     and exists (
//                      select 1 from bb_record 
//                       where bb_record.id = bb_record_invite_starmaker.record_id
//                        and bb_record.is_remove=0
//                        and bb_record.audit=1
//                   )
// ";
//             $count = $db->fetchOne($sql);
//             if ($count) {
//                 return $this->node($uid, ['code'=>0, 'message' => '您有其他邀请未点评，不能抢单，请查看您的专属邀请' ],$channel);
//             }else {
//                 // 抢单成功。
//                 $invite->starmaker_uid = $uid;
//                 $invite->new_status = 2;
//                 $invite->save();
                
//                 $id = intval( $invite->id);
//                 $sql="select * from bb_record_invite_starmaker
//                where id = {$id}
//              ";
//                 $db = \BBExtend\Sys::get_container_db_eloquent();
//                 $result = \BBExtend\DbSelect::fetchRow($db, $sql);
//                 $db::table('bb_record_invite_starmaker_log')->insert($result);
                
//                 return $this->node($uid, ['code'=>1, 'message' => '抢单成功。' ],$channel);
//             }
//         }
        
        
    }
    
    private function log($txt) {
        echo "worker: ".$txt."\n";
    }
    
   
    
}


