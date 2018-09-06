<?php
$arr = [
'tSUHu9Kp',
'123456789',
'12.34',
'e2e9e7ab47c34394817886c9c0094b33',
];
sort($arr);
var_dump($arr);
$s ='';
foreach ($arr as $v) {
    $s.=$v;
}
echo sha1($s);

