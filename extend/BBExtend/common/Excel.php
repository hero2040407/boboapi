<?php
namespace BBExtend\common;
// use think\Db;
// use BBExtend\Sys;

class Excel
{
    public $excel;
    public $row_point;
    public $success;//只要写入一行，则算成功。
    public $filename;
    
    //构造方法要写文件名
    public function __construct($filename)
    {
        $this->filename = $filename;
       // error_reporting(E_ALL);
        $this->row_point =1;
         require_once 'BBExtend/PHPExcel.php';
         file_put_contents($filename,'???');
         $objPHPExcel = $this->excel =  new \PHPExcel();
         // Set properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                                     ->setLastModifiedBy("Maarten Balliauw")
                                     ->setTitle("Office 2007 XLSX Test Document")
                                     ->setSubject("Office 2007 XLSX Test Document")
                                     ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                                     ->setKeywords("office 2007 openxml php")
                                     ->setCategory("Test result file");
        
        
        // Add some data, we will use printing features
        //echo date('H:i:s') . " Add some data\n";
        $objPHPExcel->setActiveSheetIndex(0);
        $this->success =0;
    }
    
    //输入一行
    public function write($arr)
    {
        $this->success=1;
        $objPHPExcel = $this->excel ;
        //$count = count($arr);
        $s = 'A';
        foreach ($arr as $value) {
//            $value = Public_Str::convert_encoding_reverse($value);
            $objPHPExcel->getActiveSheet()->setCellValue($s . $this->row_point, $value);
            $objPHPExcel->getActiveSheet()->getStyle($s . $this->row_point)
                        ->getFont()->setSize(10);
            $objPHPExcel->getActiveSheet()->getStyle($s . $this->row_point)
                        ->getAlignment()->setWrapText(true);            
            $s++;
        }
        
        $this->row_point++;
    }
    
    //保存到文件
    public function save()
    {
        //假如一行都没有写入，则直接退出。
        if (!$this->success) 
            return;
        $objPHPExcel = $this->excel ;    
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    //  debug($this->filename);  
        $objWriter->save($this->filename);
    }
    
    
//    public function create($filename, $arr)
//    {
//        error_reporting(E_ALL);
//        require_once 'PHPExcel.php';
//        file_put_contents($filename,'???');
//        
//        
//        $row_count = count($arr);
//        if (!$arr) {
//            return;
//        }
//        $row1 = $arr[0];
//        $col_count = count($row1);
//        
//        $objPHPExcel = new PHPExcel();
//        
//        
//        for ($i = 1; $i <= $row_count; $i++) {
//            //for ($j = 0;$j<)
//            
//            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $i);
//            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, '谢烨你好'."\n".'你好23abc');
//            $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getFont()->setSize(10);
//            $objPHPExcel->getActiveSheet()->getStyle('B'.$i)->getAlignment()->setWrapText(true);    
//        }
//        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
//        // Save Excel 2007 file
//        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
//        //$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
//        $objWriter->save(DATA_PATH . '/upload/excel/11.xlsx');
//                
//    }
//    
    
}

