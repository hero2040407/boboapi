<?php
namespace BBExtend\service\pheanstalk;


use BBExtend\service\pheanstalk\type\Type1010;


/**
 * 这是真正的任务执行1
 * 
 * 这是一个父类
 * @author xieye
 *
 */
class WorkerJobPush
{
    public $time;
    public $uid;
    public $info;
    public $type;
    
    
    public function __construct(Data $data)
    {
        $this->type = $data->get_type();
        $this->uid = $data->get_uid();
        $this->time = $data->get_time();
        $this->info = $data->get_info();
        
    }
    
    public static function factory( $data)
    {
        if ($data->type==1900) {
            return new  \BBExtend\service\pheanstalk\type\TypeTest($data);
        }
        
        if ($data->type==1010) {
            return new  \BBExtend\service\pheanstalk\type\Type1010($data);
        }
        if ($data->type==171) {
            return new  \BBExtend\service\pheanstalk\type\Type171($data);
        }
        if ($data->type==172) {
            return new  \BBExtend\service\pheanstalk\type\Type172($data);
        }
        if ($data->type==175) {
            return new  \BBExtend\service\pheanstalk\type\Type175($data);
        }
        if ($data->type==176) {
            return new  \BBExtend\service\pheanstalk\type\Type176($data);
        }
        
        if ($data->type==173) {
            return new  \BBExtend\service\pheanstalk\type\Type173($data);
        }
        if ($data->type==177) {
            return new  \BBExtend\service\pheanstalk\type\Type177($data);
        }
        if ($data->type==178) {
            return new  \BBExtend\service\pheanstalk\type\Type178($data);
        }
        
        if ($data->type==180) {
            return new  \BBExtend\service\pheanstalk\type\Type180($data);
        }
        
        
        
        if ($data->type==114) {
            return new  \BBExtend\service\pheanstalk\type\Type114($data);
        }
    }
    
    
    public function excute()
    {
        echo "---\n";
        echo  'parent' ;
        echo "---\n";
       
        
    }
}

