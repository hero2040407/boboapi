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
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Log;


// use Exception;
use think\App;
use think\Config;
use think\Console;
use think\console\Output;
use think\Lang;
// use think\Log;
use think\Response;



class Ex extends Handle
{
    
    
    
    
    /**
     * @param Exception $exception
     * @return Response
     */
    protected function convertExceptionToResponse2(Exception $exception)
    {
        // 收集异常数据
        if (App::$debug) {
            // 调试模式，获取详细的错误信息
            $data = [
                    'name'    => get_class($exception),
                    'file'    => $exception->getFile(),
                    'line'    => $exception->getLine(),
                    'message' => $this->getMessage($exception),
                    'trace'   => $exception->getTrace(),
                    'code'    => $this->getCode($exception),
                    'source'  => $this->getSourceCode($exception),
                    'datas'   => $this->getExtendData($exception),
                    'tables'  => [
                            'GET Data'              => $_GET,
                            'POST Data'             => $_POST,
                            'Files'                 => $_FILES,
                            'Cookies'               => $_COOKIE,
                            'Session'               => isset($_SESSION) ? $_SESSION : [],
                            'Server/Request Data'   => $_SERVER,
                            'Environment Variables' => $_ENV,
                            'ThinkPHP Constants'    => $this->getConst(),
                    ],
            ];
        } else {
            // 部署模式仅显示 Code 和 Message
            $data = [
                    'code'    => $this->getCode($exception),
                    'message' => $this->getMessage($exception),
            ];
            
            if (!Config::get('show_error_msg')) {
                // 不显示详细错误信息
                $data['message'] = Config::get('error_message');
            }
        }
        
        //保留一层
        while (ob_get_level() > 1) {
            ob_end_clean();
        }
        
        $data['echo'] = ob_get_clean();
        
        ob_start();
        extract($data);
        include Config::get('exception_tmpl');
        // 获取并清空缓存
        $content  = ob_get_clean();
        $response = new Response($content, 'html');
        
        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            $response->header($exception->getHeaders());
        }
        
        if (!isset($statusCode)) {
            $statusCode = 500;
        }
        $response->code($statusCode);
        return $response;
    }
    
    
    private function clear($html)
    {
        $html = preg_replace('#<script>.+?</script>#s', '', $html);
      //  $html = preg_replace( '#^.+?<body>(.+?)</body>.+$#s' , '$1', $html);
        return $html;
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
    
    
    
    
    public function render(Exception $e)
    {
        
      
     
//         $res = $this->convertExceptionToResponse2($e);
         $content = $this->get_err_msg($e);
//    //     $content = $this->clear($content);
        
         Log::record($content, 'error');

        
        return json(['code'=>0,'message' =>'服务器繁忙' ], 200);
        
        

    }
    
    
        
    
    
}


