<?php
namespace app\shop\controller;
use think\Controller;
use BBExtend\common\MysqlTool;

/**
 * 
 * 用html输出商城文档，md文档路径
 *  application/shop/view/doc/
 * 
 * 谢烨 
 */
// use \Michelf\Markdown;
use \Michelf\MarkdownExtra;
use app\shop\model\Dochtml;
class Doc  extends Controller
{
    
    public $detail ='doc';
    
    public $title='';
    
    const size=100;
    
    /**
     * 谢烨注：这是安全代码，千万保留。
     */
    public function _initialize()
    {
        if (\BBExtend\Sys::get_machine_name()=='200' || \BBExtend\Sys::get_machine_name()=='xieye' ) {
            
        }else {
            exit();
        }
    }
    
    public function index2($name='')
    {
         
    
    
       
            $doc = realpath( APP_PATH . "shop/view/doc2/");
            $file = $doc . DIRECTORY_SEPARATOR .$name .".md";
            if (PHP_OS != "Linux" ) {
                $file = mb_convert_encoding($file, "GBK","UTF-8");
            }
            if (is_file($file)) {
                $css = $this->get_css();
                $text = file_get_contents($file);
                $html = MarkdownExtra::defaultTransform($text);
//                 $this->output_html($css, $html, filemtime($file) );
                $v_img = \app\shop\model\Dochtml::display_version($file,self::size);
                $this->output_html($css, $html, filemtime($file), $v_img);
                
            }else {
                echo "文件不存在";
            }
       
    }
    
    public function index($name='')
    {
       
        $post_png ='http://resource.guaishoubobo.com/public/brandshop/post.png';
        
        //首页最简单，只需调 模板
        if (!$name) {
            $this->assign('right_list', Dochtml::get_right());
            $this->assign('middle_list', Dochtml::get_middle());
            $this->assign('left_list', Dochtml::get_left());
            $this->assign('post_png', $post_png);
            
           echo $this->fetch();
        }
        else {
            $doc = realpath( APP_PATH . "shop/view/doc/");
            $file = $doc . DIRECTORY_SEPARATOR .$name .".md";
            $this->title = $name;
            if (PHP_OS != "Linux" ) {
                $file = mb_convert_encoding($file, "GBK","UTF-8");
            }
            if (is_file($file)) {
                 $css = $this->get_css();
                 $text = file_get_contents($file);
                 $html = MarkdownExtra::defaultTransform($text);
                 
                 $v_img = \app\shop\model\Dochtml::display_version($file,self::size);
                 $this->output_html($css, $html, filemtime($file), $v_img);
            }else {
                echo "文件不存在";
            }
        }
    }
    
    public function dictdetail($name='')
    {
        //防止正式服务器泄露文档
        if ( \BBExtend\Sys::is_product_server()  ) {
            return;
        }
    
        $doc = realpath( APP_PATH . "systemmanage/view/doc/");
        $file = $doc . '/' .$name .".md";
        
        $doc2 = realpath( APP_PATH . "systemmanage/view/ds/");
        $file2 = $doc2 . '/' .$name .".md";
        
        
        if (PHP_OS != "Linux" ) {
            $file = mb_convert_encoding($file, "GBK","UTF-8");
        }
        if (is_file($file)) {
            $this->detail='doc';
            $css = $this->get_css();
            $text = file_get_contents($file);
            $html = MarkdownExtra::defaultTransform($text);
            $this->output_html($css, $html);
            $this->not_bold();
        }elseif (is_file($file2)) {
            $this->detail='ds';
            $css = $this->get_css();
            $text = file_get_contents($file2);
            $html = MarkdownExtra::defaultTransform($text);
            $this->output_html($css, $html);
            $this->not_bold();
        }
        else {
            echo "表{$name}的字典还没人写。<br><br>
            
            ";
            echo "<a href='/shop/doc/dict'>返回数据字典列表</a>";
        }
       
        
        
    }
    
    public function ds_doc()
    {
        return [
            'ds_race' => ['zhongwen'=> '大赛表', 'info'=> ''],
            'ds_dangan_config' => ['zhongwen'=> '大赛活动档案配置表', 'info'=> ''],
            'ds_dangan' => ['zhongwen'=> '大赛活动档案表', 'info'=> ''],
            'ds_lunbo' => ['zhongwen'=> '大赛轮播图表', 'info'=> ''],
            'ds_money_log' => ['zhongwen'=> '大赛报名付费日志表', 'info'=> ''],
            'ds_money_prepare' => ['zhongwen'=> '大赛报名付费预生成订单表', 'info'=> ''],
            'ds_question' => ['zhongwen'=> '大赛问答和公告表', 'info'=> ''],
            'ds_record' => ['zhongwen'=> '大赛视频表', 'info'=> ''],
            'ds_register_log' => ['zhongwen'=> '大赛报名表', 'info'=> ''],
            'ds_sponsor' => ['zhongwen'=> '大赛协助用户表', 'info'=> ''],
            'ds_show_video' => ['zhongwen'=> '大赛展示用视频表，含直播和录播', 'info'=> ''],
            
        ];
        
    }
    
    /**
     * 大赛数据字典列表。
     */
    public function dsdict()
    {
        //防止正式服务器泄露文档
        $temp = get_cfg_var('guaishou.username');
        if ($temp && in_array($temp, ['200','xieye',]) ) {
        }else {
            echo 'bu he fa.';
            exit();
        }
        $arr2= $this->ds_doc();
        $arr = MysqlTool::show_tables("ds_");
        $s = "## 大赛数据字典\n";
        $s .= "| 表名        | 中文  | 表说明 |
| -------- |:------|:------|
";
        foreach ($arr as $v) {
            $zhongwen='';
            $info='';
            if (array_key_exists($v, $arr2)) {
                $zhongwen = $arr2[$v]['zhongwen'];
                $info = $arr2[$v]['info'];
            }
            $file = $v;
            $file = "[$v](/shop/doc/dictdetail/name/{$v})";
    
            if (!is_file(APP_PATH . "systemmanage/view/ds/{$v}.md")) {
                $file.= " （暂未加入大赛数据字典）";
            }
    
            $s .= "| {$file} | {$zhongwen} | {$info} |\n";
             
        }
        $css = $this->get_css();
        $html = MarkdownExtra::defaultTransform($s);
        $this->output_html($css, $html);
        $this->not_bold();
        $this->not_underline();
        }
    
    
   
    
    /**
     * 数据字典列表。
     */
    public function dict()
    {
        //防止正式服务器泄露文档
         $temp = get_cfg_var('guaishou.username');
         if ($temp && in_array($temp, ['200','xieye',]) ) {
         }else {
             echo 'bu he fa.';
             exit();
         }
        $arr2= $this->zhongwen();
        $arr = MysqlTool::show_tables();
        $s = "## bobo数据字典\n";
        $s .= "| 表名        | 中文  | 表说明 |
| -------- |:------|:------|
";
        foreach ($arr as $v) {
            $zhongwen='';
            $info='';
            if (array_key_exists($v, $arr2)) {
                $zhongwen = $arr2[$v]['zhongwen'];
                $info = $arr2[$v]['info'];
            }
            $file = $v;
            $file = "[$v](/shop/doc/dictdetail/name/{$v})";
            
            if (!is_file(APP_PATH . "systemmanage/view/doc/{$v}.md")) {
                $file.= " （暂未加入数据字典）";
            }
            
            $s .= "| {$file} | {$zhongwen} | {$info} |\n";
           
        }
        //echo $s;
        
        
        
        $css = $this->get_css();
        $html = MarkdownExtra::defaultTransform($s);
        $this->output_html($css, $html);
        $this->not_bold();
        $this->not_underline();
//         $doc = realpath( APP_PATH . "systemmanage/view/doc/");
//         $file = $doc . DIRECTORY_SEPARATOR .$name .".md";
//         if (PHP_OS != "Linux" ) {
//             $file = mb_convert_encoding($file, "GBK","UTF-8");
//         }
//         if (is_file($file)) {
//             $css = $this->get_css();
//             $text = file_get_contents($file);
//             $html = MarkdownExtra::defaultTransform($text);
//             $this->output_html($css, $html);
//         }else {
//             echo "文件不存在";
//         }
        
    }
    
    private function output_html($css,$html,$mtime=0,$v_img='')
    {
        $request = \think\Request::instance();
        $action = $request->action();
        $title= $this->title." - 怪兽BOBO接口文档";
        if ($action == 'dictdetail') {
            if ($this->detail=='doc')
                $a = "<a class='a_return_index' href='/shop/doc/dict'>返回数据字典列表页</a>";
            else  {
                $a = "<a class='a_return_index' href='/shop/doc/dsdict'>返回大赛数据字典列表页</a>";
            }
        }else {
            $a ="<a class='a_return_index' href='/shop/doc/index'>返回怪兽BOBO接口文档首页</a>";
        }
        
        $bb='';
        if ($mtime) {
            $bb = "最后更新时间：". date("Y-m-d", $mtime)."" ;
            if (date('Y-m-d', $mtime )== date("Y-m-d") ) {
                $bb .='<img style="" src="/public/js/icon/new.gif" />';
            }elseif (date('Y-m-d', $mtime )== date("Y-m-d", time() - 1*24*3600) ) {
                $bb .='<img style="" src="/public/js/icon/yestoday.png" />';
            }
            
            $bb.=$a;
        }
        if ($v_img) {
           $bb.= '<br><br>'.  $v_img;
           $div_jiange = " <div style='height:55px' > &nbsp;</div>";
        }else {
            $div_jiange = " <div style='height:1px' > &nbsp;</div>";
        }
        
        echo  "<!DOCTYPE html>
<html>
        <head>
<title>{$title}</title>
        <script src='/public/js/jquery-1.9.1.min.js'></script>
        
        
         <script >  
   $(function(){
        //通过jQuery控制表格隔行换色，并鼠标悬停变色
        $('tr:even').addClass('tr_even');    //默认偶数行背景色，无视标题行用:gt(0)
        $('tr:odd').addClass('tr_odd');            //默认奇数数行背景色
        $('tr:gt(0)').mouseover(function(){
            $(this).addClass('tr_hover');           //通过jQuery控制实现鼠标悬停上的背景色，无视标题行用:gt(0)
        }).mouseout(function(){
            $(this).removeClass('tr_hover');       //通过jQuery控制实现鼠标悬停上的背景色
        });    
        ////////////////////////////////////////////////////////////////
    });
   </script>  
   
        {$css}
        
        <style >  
  
 .headCls{background-color:#ccc;}    /* 标题背景色 */
    .tr_even{background-color:#EBF8FF}  /* 偶数行背景色 */
    .tr_odd{}   /* 奇数行背景色 */
    .tr_hover{background-color:#fc6} /* 鼠标悬停上的背景色 */
    

code{
  font-family:\"Courier\",\"Courier New\";
}

   </style>  
        
        </head>
         
        <body>
<div class='header_fix'>
{$bb}

</div>
        {$div_jiange}
        {$html}
        <br>
        {$a}
        </body>
        
</html>";
    }
     
   
    
    private function not_underline()
    {
        echo '<style>
                 A:link{text-decoration:none;}
                </style>
                ';
    }
    
    private function not_bold()
    {
        echo '<style>
                table td {font-weight: normal;
                font-family: "Courier New", Arial, Helvetica, sans-serif; 
                }
                </style>
                ';
    }
    
    private function get_css()
    {
        $border_color="#AAA";
        $css = <<<html
<style>
body{
  margin:40px;
}

h2 {
   margin-top:60px;
}
table {
    width: 100%;
    margin-bottom: 20px;
    border-width: 1px 1px 1px medium;
    border-style: solid solid solid none;
    border-color: {$border_color} {$border_color} {$border_color}  -moz-use-text-color;
    -moz-border-top-colors: none;
    -moz-border-right-colors: none;
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    border-image: none;
    border-collapse: collapse;
}
table {
    max-width: 80%;
    background-color: transparent;
    border-spacing: 0px;
}
table td ,table th{
    padding: 8px;
    border-left: 1px solid  {$border_color};
    border-top: 1px solid  {$border_color} ;
    line-height: 20px;
    vertical-align: top;
   font-size: 16px;
   color: #2F2F2F;
              
}
            
 pre {
    display: block;
    padding: 9.5px;
    margin: 0px 0px 10px;
    font-size: 15px;
    font-family:'Times New Roman', Arial, 'Microsoft YaHei',SimHei; 
    line-height: 20px;
    word-break: break-all;
    word-wrap: break-word;
    white-space: pre-wrap;
    background-color: #F5F5F5;
    border: 1px solid rgba(0, 0, 0, 0.15);
    border-radius: 4px;
}
.a_return_index{
  padding-left:30em;
 text-decoration:none
  
}
.header_fix a{
 color:#FFF;
}

.version_img{
 border:1px #fff solid;
 width:200px;
}

.header_fix{ 
  position:fixed;  
   z-index:9999;
  padding-top:15px;
  padding-left:40px;
  padding-bottom:10px;

  color:#FFF;
  top:0;left:0;
  width:100%;
  min-height:50px;
  background-color: #ccc; opacity:1.0;
  background-image:url('/public/pic/TB1h9xxIFXXXXbKXXXXXXXXXXXX.jpg');
 background-position:0% 60%;
}
     
</style>
html;
        return $css;
    }
    
}