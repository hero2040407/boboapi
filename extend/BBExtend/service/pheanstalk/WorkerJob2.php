<?php
namespace BBExtend\service\pheanstalk;



/**
 * 这是真正的任务执行2
 * @author Administrator
 *
 */
class WorkerJob2
{
    public function excute($data)
    {
        echo "---\n";
        echo  ('job:type2:'.print_r($data, 1));
        echo "---\n";
    }
}

