<?php 
$url = "http://192.168.31.241/systemmanage/migrate/index";
$result = file_get_contents($url);
echo "\n\n---   ". $url."   ---\n" . (  $result);