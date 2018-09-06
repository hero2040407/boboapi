<?php
namespace app\apptest\controller;

use BBExtend\Sys;
use BBExtend\DbSelect;


class Temp 
{
    
   public function index()
   {
       $file =<<<ss
212
~~~ 
v=5
~~~
ss;
     echo $this->display_version($file);
       
      
   }
   
   private  function display_version($content){
       if ( preg_match('#~~~\s*v=(\d+)\s*~~~#is', $content,$matches) ){
           $version = $matches[1];
           return $this->badge_version($version);
       }else {
           return '';
       }
   }
   

   
   
   public function badge_version($v='5'){
       
       if ($v<10) {
           $css='<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
width="75" height="20">
<g shape-rendering="crispEdges">
  <path fill="#555" d="M0 0h49v20H0z"/>
  <path fill="#007ec6" d="M49 0h45v20H49z"/>
</g>

<g fill="#fff" text-anchor="middle"
font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="130"> 
  <text x="255" y="140" transform="scale(.1)" textLength="440">release</text>
  <text x="605" y="140" transform="scale(.1)" textLength="180">v'. $v .'</text>
</g> 
</svg>';
       }
       else {
           $css='<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
width="75" height="20"><g shape-rendering="crispEdges"><path fill="#555" d="M0 0h49v20H0z"/><path
fill="#007ec6" d="M49 0h45v20H49z"/></g><g fill="#fff" text-anchor="middle"
font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="110"> <text
x="255" y="140" transform="scale(.1)" textLength="390">release</text><text x="605"
y="140" transform="scale(.1)" textLength="200">v'. $v .'</text></g> </svg>';
           
       }
       $css = urlencode($css);
       $css = preg_replace('#\+#', '%20', $css);
       $s="<img width=60 src='data:image/svg+xml;utf8,". $css ."' />";
       return  $s;
   }
    
   
}






