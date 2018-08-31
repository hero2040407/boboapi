<?php

namespace app\api\controller;



/**
 * 童星排行
 * 
 * @author xieye
 *
 */
use Exception;
use think\exception\Handle;
use think\Log;


class Ex extends Handle
{
    
    
    
    public function render(Exception $e)
    {
        
        $content = $this->get_err_msg($e);
        Log::record($content, 'error');
        
        return json(['code'=>0,'message' =>'服务器繁忙' ], 200);
        
    }
    
    
    
    
    private function get_err_msg(Exception $exception){
        $s='';
        $code = $this->getCode($exception);
        $name = get_class($exception);
        $file    = $exception->getFile();
        $line    = $exception->getLine();
        $message = $this->getMessage($exception);
        
        $source  = $this->getSourceCode($exception);
        $code_str = '';
//         'first'  => $first,
//         'source' => 
        if ( $source ) {
            $code_first = $source['first'];
            $code_arr = $source['source'];
            
            foreach ( $code_arr as $codes ) {
//                 $code_str.="　　　　line {$code_first}　　<font>";
                if ($code_first==$line) {
                    $code_str.="　　　　line {$code_first}  ->　<font style='font-weight:bold'>";
                }else {
                    $code_str.="　　　　line {$code_first}　　<font>";
                }
                $codes = rtrim($codes);
                $code_str.=$codes."</font>\n";
                $code_first++;
            }
        }
        
        // [0] InvalidArgumentException in App.php line 250
      //  $s.= "{$message}\n[{$code}] {$name} in {$file} line {$line}";
        $s.= "[{$code}] {$name} in {$file} line {$line}\n";
        if ($code_str) {
            $s .=$code_str;
        }
        $s .= "Stack trace:\n";
        // 最后补上栈信息。
        $s .= $exception->getTraceAsString();
        
        // 提交数据
        $s .= "\n　　\$_GET:\n";
        $s.= var_export($_GET,1);
        $s .= "\n　　\$_POST:\n";
        $s .= var_export($_POST, 1);
        
        return $s;
        
    }
    
    
  
    
    
}


