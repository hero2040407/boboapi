<?php
namespace app\api\controller;
use BBExtend\Sys;
use BBExtend\common\HtmlTable;

use think\Controller;
use think\Db;
class Download extends Controller
{

    public function html()
    {
        $s=<<<html
        <form enctype="multipart/form-data" action="https://bobo.yimwing.com/api/file/add" method="POST">
    <!-- MAX_FILE_SIZE must precede the file input field -->
    
   
    Send this file: <input name="uploadfile" type="file" />
    <input type="submit" value="Send File" />
</form>
html;
        echo $s;
    }


    public function html2()
    {
        $s=<<<html
        <form enctype="multipart/form-data" action="https://bobo.yimwing.com/api/file/adds" method="POST">
    <!-- MAX_FILE_SIZE must precede the file input field -->
    
  
    Send this file: 
                <input name="uploadfile[]" type="file" />
    Send this file: 
                <input name="uploadfile[]" type="file" />
                
                
    <input type="submit" value="Send File" />
</form>
html;
        echo $s;
    }



    public function add($version=0)
    {
        $table='bb_client_log';
        $request = \think\Request::instance();
        $user_agent =$request->header("user-agent");
        if (!$user_agent) {
            $user_agent = '';
        }
        //    var_dump($_FILES);
        //    return;
        $base_path = '/var/www/html/runtime/temp/';
        $tmp_name = $_FILES["uploadfile"]["tmp_name"];
        $name = $_FILES["uploadfile"]["name"];
        $uploadfile = $base_path . (time(). mt_rand(100000, 999999));
        $isSave = move_uploaded_file($tmp_name, $uploadfile);

        $type='';
        if (preg_match('#zip$#', $name )) {
            $type='zip';
        }

        $db = Sys::get_container_db();
        $db->insert($table, [
            'ip' => $request->ip(),
            'datestr' => date("Ymd"),
            'create_time' => time(),
            'content' => $uploadfile,
            'orginal_name' => $name,
            'agent' => $user_agent,
            'type' => $type,
            'version' => intval($version),
        ]);
        return ['code'=>1];
    }


    public function adds($version=0)
    {
        $table='bb_client_log';
        $request = \think\Request::instance();
        $user_agent =$request->header("user-agent");
        if (!$user_agent) {
            $user_agent = '';
        }
        //  var_dump($_FILES);
        //    return;
        $base_path = '/var/www/html/runtime/temp/';

        foreach ($_FILES["uploadfile"]["tmp_name"] as $k=>$v) {

            $tmp_name = $_FILES["uploadfile"]["tmp_name"][$k];
            $name = $_FILES["uploadfile"]["name"][$k];
            $uploadfile = $base_path . (time(). mt_rand(100000, 999999));
            $isSave = move_uploaded_file($tmp_name, $uploadfile);


            $db = Sys::get_container_db();
            $db->insert($table, [
                'ip' => $request->ip(),
                'datestr' => date("Ymd"),
                'create_time' => time(),
                'content' => $uploadfile,
                'orginal_name' => $name,
                'agent' => $user_agent,
                'version' => intval($version),

            ]);
        }
        return ['code'=>1];
    }

    public function content($id){
        $db = Sys::get_container_db();
        $id=intval($id);
        $sql="select * from bb_client_log where id =".intval($id);
        $row=$db->fetchRow($sql);

        $content = file_get_contents($row['content']);
        $a = "<a href='/api/file/remove/id/{$id}'>删除当前日志</a>";
        echo "<h2>log id:{$id}，文件名：{$row['orginal_name']}，操作：{$a}</h2>";
        echo "
       <pre>        
       <div>{$content}</div>
       </pre>
       ";
    }

    public function download($id){
        $db = Sys::get_container_db();
        $id=intval($id);
        $sql="select * from bb_client_log where id =".intval($id);
        $row=$db->fetchRow($sql);

        $filename = $row['content'];
        set_time_limit(0);
        if ($filename && file_exists($filename)) {
            //  $file_type = 'application/vnd.ms-excel';
            ob_end_clean();
            // header("Content-Type: " . $file_type    ."\n");
            header("Content-disposition: attachment; filename=\"".
                $row['orginal_name']  ."\"\n");
            header("Content-Length: " . filesize($filename) ."\n");
            readfile($filename);

        }
    }


    public function remove($id)
    {  Sys::display_all_error();
        $db = Sys::get_container_db();
        $id=intval($id);
        $sql = "delete from bb_client_log where id =".intval($id);
        $db->query($sql);
        // echo 22;

        $this->redirect(  \think\Url::build('/api/file/index')  );
    }

    public function removes($version)
    {
        $version = intval($version);
        Sys::display_all_error();
        $db = Sys::get_container_db();
        // $id=intval($id);
        $sql = "delete from bb_client_log where version ={$version}";
        $db->query($sql);
        // echo 22;

        $this->redirect(  \think\Url::build('/api/file/index')  );
    }

    public function index($startid=0,$length=10000)
    {
        $startid=intval($startid);
        $length = intval($length);
        $db = Sys::get_container_db();

        $DangAnList= Db::table('bb_client_log')->field('id,ip,agent,create_time,orginal_name,type,version')
            ->order('id desc')->select();

        $DangAnTitleList=array('id','ip','agent','create_time','orginal_name','type','version');

     
       $this->ExportPlayerDataU($DangAnList,$DangAnTitleList);
    }
    
    private function ExportPlayerDataU($DangAnList, $DangAnTitleList, $filename = 'report')
    {
    
        $xlsTitle = iconv('utf-8', 'gb2312', "report");//文件名称
        $fileName = $filename.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($DangAnTitleList);
        $dataNum = count($DangAnList);
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
    
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $DangAnTitleList[$i]);
        }
        //    // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $DangAnList[$i][$DangAnTitleList[$j]]);
            }
        }
    
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
    

   
}


class url_help{
    public function url($page){
        return '/api/file/index?page=' . intval($page);
    }
}



