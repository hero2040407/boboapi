<?php
namespace BBExtend\service;
interface NodeInterface{
    
    /**
     * 发送HTTP请求方法
     * @param  string $url    请求URL
     * @param  array  $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @return array  $data   响应数据
     */
    public function http_Request($url, $params, $method = 'GET',
            $header = array("Content-type: text/html; charset=utf-8"), $multi = false);
    
}