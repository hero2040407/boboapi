<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;


class Phean {
    
    public function run(){
        \BBExtend\Sys::display_all_error();
        //这里得在服务器命令行执行。
        $worker = new \BBExtend\service\pheanstalk\Worker();
        $worker->run();
    }
    // 测试用
    public function dptest(){
        \BBExtend\Sys::display_all_error();
        //这里得在服务器命令行执行。
        $worker = new \BBExtend\service\pheanstalk\Workerdptest();
        $worker->run();
    }
    
    public function run_dianping(){
        \BBExtend\Sys::display_all_error();
        //这里得在服务器命令行执行。
        $worker = new \BBExtend\service\pheanstalk\Workerdp();
        $worker->run();
    }
    
    public function run_dasai(){
        \BBExtend\Sys::display_all_error();
        //这里得在服务器命令行执行。
        $worker = new \BBExtend\service\pheanstalk\Workerdasai();
        $worker->run();
    }
    
    /**
     * 这是用于测试服的。
     */
    public function close_test(){
        \BBExtend\Sys::display_all_error();
        //这里得在服务器命令行执行。
//         $command ="ps aux|grep /usr/bin/supervisord";
//         $out = shell_exec ( $command );
//         //         echo $out;
//         $arr= preg_split('/[\n]+/s', $out);
//         //         dump($arr);
//         $result='';
//         foreach ($arr as $v) {
//             if (preg_match( '#/etc/supervisord.conf#', $v )) {
//                 $result =  $v;
//                 break;
//             }
//         }
//         $id = preg_replace('#^root\s+(\d+)\s+.+$#', '$1', $result);
//         echo $result;
//         echo "\n";
//         echo "{$id}\n";
        $command ="service supervisord stop";
        $out = shell_exec ( $command );
        
        $ids = $this->get_child_ids_test();
        foreach ($ids as $id) {
            $command ="kill {$id}";
            $out = shell_exec ( $command );
        }
        sleep(3);
        echo "all ok\n";
    }
    
    
    
    
    private function get_child_ids_test(){
        $new=[];
        
        $command ="ps aux|grep index.php";
        $out = shell_exec ( $command );
        //         echo $out;
        $arr= preg_split('/[\n]+/s', $out);
        //         dump($arr);
        //$result='';
        foreach ($arr as $v2) {
            $v = trim($v2);
            if (preg_match( '#/systemmanage/phean/run$#', $v )) {
                $id = preg_replace('#^root\s+(\d+)\s+.+$#', '$1', $v);
                $new[]=  $id;
            }
//             if (preg_match( '#/command/worker/start$#', $v )) {
//                 $id = preg_replace('#^root\s+(\d+)\s+.+$#', '$1', $v);
//                 $new[]=  $id;
//             }
//             if (preg_match( '#/command/worker22/start$#', $v )) {
//                 $id = preg_replace('#^root\s+(\d+)\s+.+$#', '$1', $v);
//                 $new[]=  $id;
//             }
            if (preg_match( '#/systemmanage/phean/run_dianping$#', $v )) {
                $id = preg_replace('#^root\s+(\d+)\s+.+$#', '$1', $v);
                $new[]=  $id;
            }
        }
        return $new;
        
    }
    
    
    
    
    
    
    public function close(){
        \BBExtend\Sys::display_all_error();
        //这里得在服务器命令行执行。
        $command ="ps aux|grep /usr/bin/supervisord";
        $out = shell_exec ( $command );
//         echo $out;
        $arr= preg_split('/[\n]+/s', $out);
//         dump($arr);
        $result='';
        foreach ($arr as $v) {
            if (preg_match( '#/etc/supervisord.conf#', $v )) {
                $result =  $v;
                break;
            }
        }
        $id = preg_replace('#^root\s+(\d+)\s+.+$#', '$1', $result);
        echo $result;
        echo "\n";
        echo "{$id}\n";
        $command ="kill {$id}";
        $out = shell_exec ( $command );
        
        $ids = $this->get_child_ids();
        foreach ($ids as $id) {
            $command ="kill {$id}";
            $out = shell_exec ( $command );
        }
        sleep(3);
        echo "all ok\n";
    }
    
    
    private function get_child_ids(){
        $new=[];
        
        $command ="ps aux|grep index.php";
        $out = shell_exec ( $command );
        //         echo $out;
        $arr= preg_split('/[\n]+/s', $out);
        //         dump($arr);
        //$result='';
        foreach ($arr as $v2) {
            $v = trim($v2);
            if (preg_match( '#/systemmanage/phean/run$#', $v )) {
                $id = preg_replace('#^root\s+(\d+)\s+.+$#', '$1', $v);
                $new[]=  $id;
            }
            if (preg_match( '#/command/worker/start$#', $v )) {
                $id = preg_replace('#^root\s+(\d+)\s+.+$#', '$1', $v);
                $new[]=  $id;
            }
            if (preg_match( '#/command/worker22/start$#', $v )) {
                $id = preg_replace('#^root\s+(\d+)\s+.+$#', '$1', $v);
                $new[]=  $id;
            }
            if (preg_match( '#/systemmanage/phean/run_dianping$#', $v )) {
                $id = preg_replace('#^root\s+(\d+)\s+.+$#', '$1', $v);
                $new[]=  $id;
            }
            if (preg_match( '#/systemmanage/phean/run_dasai$#', $v )) {
                $id = preg_replace('#^root\s+(\d+)\s+.+$#', '$1', $v);
                $new[]=  $id;
            }
        }
        return $new;
        
    }
    
    
    
}