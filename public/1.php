<?php 
class A
{
    function display(){
        echo "this is a A<br>";
    }
    
}


class B
{
    function display(){
        echo "this is a B<br>";
    }
    
}

$aa = new A();
// $aa->display();

$bb = new B();
// $bb->display = function (){ echo "in display"; };


$bb->display();
