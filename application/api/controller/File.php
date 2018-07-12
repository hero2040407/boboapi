<?php
namespace app\api\controller;
use BBExtend\Sys;
use BBExtend\common\HtmlTable;

use think\Controller;

/**
 * 安卓日志控制器
 * 
 * @author xieye
 *
 */
class File extends Controller
{
    /**
     * 安卓日志列表
     *
     * @param number $startid
     * @param number $length
     */
    public function index($startid=0,$length=10)
    {
        // Sys::display_all_error();
        $startid=intval($startid);
        $length = intval($length);
        $db = Sys::get_container_db();
         
        $select = $db->select();
        //  echo ini_get('include_path');
        require_once( APP_PATH .'/../extend/Smarty/Smarty.class.php');
        $smarty = new \Smarty();
    
        $smarty->template_dir = '/var/www/html/application/api/view/';
        $smarty->compile_dir = '/var/www/html/runtime/smarty/templates_c/';
        $smarty->config_dir = '/var/www/html/runtime/smarty/configs/';
        $smarty->cache_dir = '/var/www/html/runtime/smarty/cache/';
         
        $select->from('bb_client_log',array('id','ip','agent','create_time','orginal_name','type','version'))
        ->order('id desc');
        $paginator = \Zend_Paginator::factory($select);
        $paginator->setCurrentPageNumber(isset($_REQUEST['page']) ? $_REQUEST['page'] : 1); //设置当前页数2
        $paginator->setItemCountPerPage(10); //设置每页的条数
        $paginator->setPageRange(10);  //设置显示几个链接
        //echo $paginator->count();
        $arr = $paginator->getIterator();
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        foreach ($arr as $k=>$v) {
            $version = intval($v['version']);
            $arr[$k]['view'] ="<a href='#'
            onclick='click1({$v['id']});return false'
            >查看</a>";
             
            $arr[$k]['download'] ="<a href='/api/file/download/id/{$v['id']}' >下载</a>";
             
            $arr[$k]['del'] ="<a target='_blank' href='/api/file/remove/id/{$v['id']}/page/{$page}' >删除</a>";
            $arr[$k]['dels'] ="<a href='/api/file/removes/version/{$version}' >删除整个版本</a>";
        }
        //赋值
        $smarty->assign("pageinfo",$paginator->getPages()); //默认Sliding
        $url_obj = new url_help();
        $smarty->assign("urlhelp",$url_obj);
        //引用模板文件
        echo "
        <!DOCTYPE html>
        <html lang=\"en\">
        <head>
        <meta charset=\"utf-8\">
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <script src=\"/race/js/jquery-2.0.3.min.js\"></script>
        <script>
        function click1(id){
         
        location.href='#f1';
        $.get('/api/file/content/id/'+id+'/page/'+{$page},function(data,status){
        $('#div1').html(data);
        location.href='#f1';
        //alert('Data: ' + data + 'Status: ' + status);
    });
    
    }
     
    </script>
     
     
    </head>
    <body>
    <center><h1>bobo客户端调试日志</h1> <a href='/api/download/index'>下载excel</a> </center>";
        $smarty->display('file_index.tpl');
         
        foreach ($arr as $k=>$v) {
            $arr[$k]['create_time'] = date("Y-m-d H:i:s",$arr[$k]['create_time']);
        }
         
        $obj = new HtmlTable(
                array('id','ip','agent','time','original_name','type','version','view','download','del','del_version'
                ),$arr
                );
         
        echo $obj->to_html();
         
        $smarty->display('file_index.tpl');
        echo "
           <br><br><br><br><br><br><br>
               <a name='f1' id='f1'></a>
       <div id='div1' >
    
    
    
        
       </div>
       </body>
       </html>
               ";
    }
    
    
    
    /**
     * 一个单独的上传页
     */
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
    
    /**
     * 另一个单独的上传页
     */
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
    
    
  /**
   * 上传日志
   * @param number $version
   */
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
   
   /**
    * 上传多个日志
    * @param number $version
    */
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
   
   /**
    * 单个日志详情
    * @param unknown $id
    * @param number $page
    */
   public function content($id,$page=1){
       $db = Sys::get_container_db();
       $id=intval($id);
       $sql="select * from bb_client_log where id =".intval($id);
       $row=$db->fetchRow($sql);
       
       $content = file_get_contents($row['content']);
       $a = "<a href='/api/file/remove/id/{$id}/page/{$page}'>删除当前日志</a>";
       echo "<h2>log id:{$id}，文件名：{$row['orginal_name']}，操作：{$a}</h2>";
       echo "
       <pre>        
       <div>{$content}</div>
       </pre>
       ";
   }
   
   /**
    * 下载
    * @param unknown $id
    */
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
   
   /**
    * 删除单个
    * @param unknown $id
    * @param number $page
    */
   public function remove($id,$page=1)
   {  Sys::display_all_error();
       $db = Sys::get_container_db();
       $id=intval($id);
       $sql = "delete from bb_client_log where id =".intval($id);
       $db->query($sql);
      // echo 22;
        
       $this->redirect(  '/api/file/index?page='.$page  );
   }
   
   /**
    * 删除多个
    * @param unknown $version
    */
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
   
    
}


class url_help{
    public function url($page){
        return '/api/file/index?page=' . intval($page);
    }
}



