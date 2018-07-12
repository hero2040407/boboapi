<?php
require 'd:/workspace_utf8/guai2/extend/BBExtend/common/Str.php';
$handle = fopen ( "php://stdin", "r" );

// 这里获得命令行参数
if (count ( $argv ) > 1)
    $date = $argv [1];
else
    $date = '2010-01-01';
    
    // 迭代
$j = 0;
while ( ! feof ( $handle ) ) {
    $buffer = fgets ( $handle );
    process ( $buffer );
}
// 关闭输入流，并结束
fclose ( $handle );

function process($str){
    echo  \BBExtend\common\Str::u2g($str) ;
}
