<?php

/**
 * 
 * 
 * @author 谢烨
 */
namespace app\systemmanage\controller;
class Img
{
    public function output($v=9){
        header('Content-Type:image/svg+xml');
        $css='<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
width="75" height="20"><g shape-rendering="crispEdges"><path fill="#555" d="M0 0h49v20H0z"/><path
fill="#007ec6" d="M49 0h45v20H49z"/></g><g fill="#fff" text-anchor="middle"
font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="110"> <text
x="255" y="140" transform="scale(.1)" textLength="390">release</text><text x="605"
y="140" transform="scale(.1)" textLength="150">v'. $v .'</text></g> </svg>';
      //  $css = urlencode($css);
     //   $css = preg_replace('#\+#', '%20', $css);
        echo $css;
    }
    
    
    public function img2($v='5'){
//         $src = $this->output($v);
        echo $this->badge_version($v);
    }
    
    
    public function badge_version($v='5'){
  
        if ($v<10) {
        $css='<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
width="75" height="20"><g shape-rendering="crispEdges"><path fill="#555" d="M0 0h49v20H0z"/><path 
fill="#007ec6" d="M49 0h45v20H49z"/></g><g fill="#fff" text-anchor="middle" 
font-family="DejaVu Sans,Verdana,Geneva,sans-serif" font-size="110"> <text 
x="255" y="140" transform="scale(.1)" textLength="390">release</text><text x="605" 
y="140" transform="scale(.1)" textLength="150">v'. $v .'</text></g> </svg>';
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
        $s="<img src='data:image/svg+xml;utf8,". $css ."' />";
        return  $s;
    }
    
    
        
    
    
    
    
    
   
}