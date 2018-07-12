#!/usr/bin/php
<?php
//谢烨，在cygwin下的测试服务器，修改文件。

if (PHP_SAPI == "cli") {
    
}
$cmd ="find /cygdrive/c/cygwin64/tmp/guai2 -name {$argv[1]}";

$out = trim(shell_exec($cmd));
$arr = preg_split('#\s+#', $out);
if (count($arr) > 1 ) {
    echo "too much ...\n";exit();
}
$file = $arr[0];
if ($file) {
    $filename = preg_replace('#^.+/([^/]+)$#', '$1', $file);
    $temp1 = preg_replace('#/cygdrive/c/cygwin64/tmp/guai2#', '/cygdrive/d/bobo', $file);
    $cmd = "cp -f {$file} {$temp1}";
    $out = trim(shell_exec($cmd));
    
    echo $out;
}else {
    echo "file is null";
}
echo "\n";

