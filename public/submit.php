<?php 

    $name = $_GET['name'];
   
    $phone = $_GET['phone'] ;
    $org = $_GET['org'] ;
    $marks = $_GET['marks'];
    $line = $name.",".$phone.",".$org.",".$marks ;
    ini_set('max_execution_time', 300);

    $fp = fopen('../submit.csv', 'a+');
    fputcsv($fp, array($name,$phone,$org,$marks));
    fclose($handle);
    fclose($fp);
	
	//返回数据 jsonp
	header('Content-type: text/javascript');  
	$callback = $_GET['callback'];
	echo $callback.'({"code":200})';
	exit;
?>