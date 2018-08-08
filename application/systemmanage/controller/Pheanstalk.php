<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;
use \BBExtend\service\pheanstalk\Worker;
class Pheanstalk 
{
    public function run()
    {
        $worker = new Worker();
        $worker->run();
    }
    
    
   
}