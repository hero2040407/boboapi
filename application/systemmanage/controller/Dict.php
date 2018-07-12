<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;
use BBExtend\Sys;
use BBExtend\DbSelect;

use Illuminate\Database\Capsule\Manager;

class Dict {
    
    public function index ($type='' )
    {
        \BBExtend\Sys::display_all_error();
        // 保证正式服和测试服都不能访问。
        if ((Sys::get_machine_name()!='xieye') && 
                (Sys::get_machine_name()!='200')
                ) {
            exit;
        }
       
        $db = Sys::get_container_db_eloquent( ); // 这是数据库连接
        
//         if (Sys::get_machine_name()=='200'){
            
//             $db = new Manager ();
//             $db->addConnection ( [
//                     'driver' => 'mysql',
//                     'host' => '127.0.0.1',
//                     'database' => 'bobo',
//                     'username' => 'root',
//                     'password' => '',
//                     'charset' => 'utf8mb4',
//                     'collation' => 'utf8mb4_unicode_ci',
//                     'prefix' => ''
//             ] );
//             $db->setAsGlobal ();
//             $db->bootEloquent ();
            
//         }
        
      
        
        $db_name = "bobo"; // 这是数据库名
        
        // 先查出表的元数据，和字段的元数据。
        $sql = "
select table_name,table_comment from information_schema.tables
where table_schema='{$db_name}'
order by table_name asc
";
        $table_arr = DbSelect::fetchAll( $db, $sql );
        
        $new =[];
    //    echo 12;exit;
        
        // 谢烨，这里 的type=race表示专用的大赛列表 html数据字典。
        $type_race_describe='';
        
        if ($type=='race') {
            foreach ( $table_arr as $v ) {
                if ( preg_match('/^ds/' , $v['table_name']) ) {
                    $new[]= $v;
                }
            }
            $table_arr = $new;
            
            $type_race_describe="
<pre>
ds_dangan表： ds_id 真大赛id

ds_lunbo 表： ds_id 真大赛id

ds_monty_log： ds_id 真大赛id，zong_ds_id 字段未使用。

ds_monty_prepare： ds_id 真大赛id，zong_ds_id 字段未使用。

ds_request: ds_id 真大赛id

ds_race_field: race_id 真大赛id

ds_race_message: ds_id 真大赛id，field_id，线下赛区

ds_record: ds_id 真大赛id

ds_register_log: zong_ds_id 真大赛id， ds_id 老数据对应 ds_race 表 的level=2的id，新数据：如果线上0 ，线下对于 ds_race_field表主键。

ds_show_video: ds_id 真大赛id

ds_sponsor（大赛主办方表）ds_id 真大赛id
</pre>
";
            
        }
        
        
        
        $table_arr = $this->update_table( $table_arr );
        $sql = "
SELECT
    T.TABLE_NAME AS 'table_name',
    T. ENGINE AS 'engine',
    C.COLUMN_NAME AS 'column_name',
    C.COLUMN_TYPE AS 'column_type',
    C.COLUMN_COMMENT AS 'column_comment'
FROM
    information_schema.COLUMNS C
INNER JOIN information_schema.TABLES T ON C.TABLE_SCHEMA = T.TABLE_SCHEMA
AND C.TABLE_NAME = T.TABLE_NAME
WHERE
    T.TABLE_SCHEMA = '{$db_name}'
";
        $column_arr = DbSelect::fetchAll( $db, $sql );
        $column_arr = $this->my_comment( $column_arr );
        
        // 构造表的索引
        $table_list_str = '';
        foreach ($table_arr as $v) {
            $table_list_str .= '<li><a href="#' . $v['table_name'] . '">' .
                    $v['table_name'] . "（{$v['table_comment']}）" . '</a></li>' . "\n";
        }
        
        // 构造数据字典的内容
        $table_str = '';
        foreach ($table_arr as $v) {
            $table_name = $v['table_name'];
            $table_comment = $v['table_comment'];
            $table_str .= <<<html
<a href="#header"><p class="normal pa">回到首页</p></a>


<p class='table_jiange'><a name='{$table_name}'>&nbsp</a></p>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
<tr>
	<td  width="70%"  class="headtext"
        align="left" valign="top">&nbsp;{$table_name}（{$table_comment}）</td>
	<td  width="30%" class="headtext"
        align="right"
        >&nbsp;</td>
        
<tr>
</table>

<table width="100%" cellspacing="0" cellapdding="2" class="table2" >
<tr>
	<td align="center" width='15%' valign="top" class="fieldcolumn">字段</td>
	<td align="center" width='15%' valign="top" class="fieldcolumn">类型</td>
	<td align="center" width='70%'  valign="top" class="fieldcolumn">注释</td>
</tr>
html;
            foreach ($column_arr as $vv) {
                if ($vv['table_name'] == $table_name) {
                    $table_str .= <<<html
<tr>
	<td align="left"  width='15%' ><p class="normal">{$vv['column_name']}</p></td>
	<td align="left"  width='15%' ><p class="normal">{$vv['column_type']}</p></td>
	<td align="left"  width='70%' ><p class="normal">{$vv['column_comment']}</p></td>
</tr>
html;
                }
            }
            $table_str .= "</table>\n\n";
        }
        
        // 开始构造整个数据字典的html页面
        $html = <<<html
<html>
<head>
<title>{$db_name}数据字典</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<style type="text/css">
<!--
.toptext {font-family: verdana; color: #000000; font-size: 20px; font-weight: 600; width:550;  background-color:#999999; }
.normal {  font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 16px; font-weight: normal; color: #000000}
.normal_ul {  font-family: Verdana, Arial, Helvetica, sans-serif;
   font-size: 12px; font-weight: normal; color: #000000}
.fieldheader {font-family: verdana; color: #000000; font-size: 16px; font-weight: 600; width:550;  background-color:#c0c0c0; }
.fieldcolumn {font-family: verdana; color: #000000; font-size: 16px; font-weight: 600; width:550;  background-color:#ffffff; }
.header {background-color: #ECE9D8;}
.headtext {font-family: verdana; color: #000000; font-size: 20px; font-weight: 600;    }
BR.page {page-break-after: always}
//-->
</style>

<style>

  a:link{text-decoration:none;}
  a:visited{text-decoration:none;}
  a:active{text-decoration:none;}
  
  body {
    padding:20px;
  }
  
  #ul2 {
    margin:0;
   padding:0;
  }
  #ul2 li {
	display:inline;
	float:left;
	margin:5 50px;
	padding:0px 0px;
	
    width:500px;
	background-color:#Eee;
	border:1px #bbb dashed;
	
  }
  #ul2 li a{
    display:block;
    font-size:14px;
    color:#000;
    
    padding:10px 5px;
    font-weight:bolder;
  }
  
  #ul2 li:hover {
    background-color:#73B1E0;
  }
  #ul2 li:hover a {
    color:#FFF;
  }
  
  #div2 {
    clear:both;
	margin:20px;
  }
  .table2 td {
    padding:5px 10px;
  }
  .table2 tr:hover td {
    background-color:#73B1E0;
  }
  .table2 tr:hover td p{
    color:#FFF;
  }
  
  .table2 {border-right:1px solid #aaa; border-bottom:1px solid #aaa}
  .table2  td{border-left:1px solid #aaa; border-top:1px solid #aaa}
  
  .table2 tr:nth-child(even){background:#F4F4F4;}
  
  
  .headtext {
    padding:10px;
  }
  p.pa{
    color:blue;
  }
  .table_jiange{
    height:1px;
    margin:20px;
    padding:0;
  }
  
</style>
</head>

<body bgcolor='#ffffff' topmargin="0">
<table width="100%" border="0" cellspacing="0" cellpadding="5">
  <tr>
    <td class="toptext"><p align="center">{$db_name}数据字典</td>
  </tr>
</table>

<a name="header">&nbsp</a>
<ul id='ul2'>
{$table_list_str}
</ul>

<div style='clear:both;'></div>
{$type_race_describe}


<div id="div2"></div>
<br class=page>

{$table_str}

<a href="#header"><p class="normal">回到首页</a>
<h1 width="100%">
</body>
</html>
html;
        echo $html;
    }
  
    
    
    /**
     * 自定义注释，可以完美覆盖表中的注释。
     *
     * @return string[][]
     */
    private function my_comment_list ( )
    {
        $file = __DIR__ . '/' . 'dict_comment.md';
        $arr2 = file($file);
        $arr=[];
        foreach ($arr2 as $v) {
            if ( preg_match('#|#', $v) ) {
               $temp2 = explode('|', $v);
               if (isset( $temp2[3] )) {
                  $temp = [
                    'table_name' => trim($temp2[1]),
                    'column_name' => trim($temp2[2]),
                    'column_comment' => $temp2[3]
                  ];
                  $arr[]= $temp;
               }
            }
        }
        
        $temp = [
                'table_name' => 'bb_config_str',
                'column_name' => 'config',
                'column_comment' => '
键，一般用英文或拼音，成就的键类似huodong0,<br>
 键index_window_open  首页弹窗， 1打开 0关闭<br>
 键index_window_link  首页弹窗， html链接<br>
 键index_window_style  首页弹窗，1全屏，2弹框带边框<br>
 键index_window_type   首页弹窗，1公告<br>
type=13 <br>
键 race_msg_register   报名成功发送的公告<br>
键 race_msg_promote    晋级成功发送的公告<br>


'
        ];
        $arr[]= $temp;
        
        
        return $arr;
    }
    
    
    
    private function my_comment_table ( )
    {
        $file = __DIR__ . '/' . 'dict_table.md';
        $arr2 = file($file);
        $arr=[];
        foreach ($arr2 as $v) {
            if ( preg_match('#|#', $v) ) {
                $temp2 = explode('|', $v);
                if (isset( $temp2[2] )) {
                    $temp = [
                            'table_name' => trim($temp2[1]),
                            'table_comment' => $temp2[2],
                    ];
                    $arr[]= $temp;
                }
            }
        }
        return $arr;
        
     
        return $arr;
    }
    
    private function my_comment ( $arr )
    {
        $my_table = $this->my_comment_list( );
        foreach ($arr as $k => &$v) {
            foreach ($my_table as $my) {
                if ($v['table_name'] == $my['table_name'] &&
                        $v['column_name'] == $my['column_name']) {
                            $v['column_comment'] = $my['column_comment'];
                        }
            }
        }
        return $arr;
    }
    
    private function update_table ( $arr )
    {
        $my_table = $this->my_comment_table( );
        foreach ($arr as $k => &$v) {
            foreach ($my_table as $my) {
                if ($v['table_name'] == $my['table_name'] ) {
                            $v['table_comment'] = $my['table_comment'];
                }
            }
        }
        return $arr;
    }
    
    
}