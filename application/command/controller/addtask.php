<?php
ini_set ( 'error_reporting', 6143 );
ini_set('display_errors', 1);
echo 1;
require __DIR__ .'/../../../extend/lib/vendor/autoload.php';
 Resque::setBackend('127.0.0.1:6380');
      $args = array(
          'name' => '张三：'
      );
      Resque::enqueue('default', 'Myjob', $args);
      