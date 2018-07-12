<?php
/**
 * 该接口定义了一个设置 短视频 连续上传的间隔
 * 
 * 
 * 
 * User: 谢烨
 */
namespace BBExtend\video;

interface Ilimit
{
    public function can_upload();
    
}