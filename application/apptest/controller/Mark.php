<?php
namespace app\apptest\controller;
// 12
use \Michelf\Markdown;
use \Michelf\MarkdownExtra;
class Mark
{
   
   public function index()
   {
      
       $css = $this->get_css();
       
       $text = <<<html
# 213333  
# 12355谢烨

1. Red
244. Green
3. Blue

| ABCD | EFGH | IJKL |
| -----|:----:| ----:|
| a    | b    | c        |
| d    | e    |  f   |
| g    | h    |   i  |
               
               
| Tables        | Are           | Cool  |
| ------------- |:-------------:| -----:|
| col 3 is      | right-aligned | $1600 |
| col 2 is      | centered      |   $12 |
| zebra stripes | are neat      |    $1 |
html;
       $html = MarkdownExtra::defaultTransform($text);
       
     $this->output_html($css, $html);
       
       
   }
   
   private function output_html($css,$html)
   {
       echo  "<!DOCTYPE html>
<html>
       <head>
       {$css}
       </head>
       
       <body>
       {$html}
       </body>
        
</html>";
       
   }
   
   private function get_css()
   {
       $border_color="#AAA";
       $css = <<<html
<style>

body{
  margin:20px;
  padding:20px;
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
              font-weight: bolder;
}
</style>
html;
    return $css;
   }
   
}
