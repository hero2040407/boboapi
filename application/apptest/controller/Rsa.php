<?php
namespace app\apptest\controller;


class Rsa
{
   
    public function test2()
    {
        $i = -1;
        if ($i) {
            echo "ok";
        }else {
            echo "false";
        }
    }
    
    public function test()
    {
        $data =[
            'a'=>1,
            'b'=>2,
            
        ];
        $help = new \BBExtend\pay\alipay\AlipayHelp();
        return $help->sign($data);
        
    }
    
   public function index()
   {
       
       $private_key = '-----BEGIN RSA PRIVATE KEY-----
MIICWwIBAAKBgQDBZkDm6jZ3hGTNVH3XlOSpGBUZ7Hck1Im3EDUNUm2v94AHBpZj
5yGN3XnVVzlzyU6KfFZk/Q36BBKogxPTXrwht7i6f9teq1Jk03f+umEqTF59Yb+P
FDnJKJvSVe8lCQbhJCtxE8DF5Wjf/AY6TVOpu+FWzaypWak/p7nuCa61QQIDAQAB
AoGAdyuJ7H//pe+3qWpZzMBbkfJb9khmNhSc82eSOS5Elnx8sFeXzeF7JI6HZzVD
Gpy9v8nT9pCTzy45TQrP6ZvjchUCJ1RES6Kvnna85XAznnnBoMWxTKZo98N5WIII
f/CH1mvCBPc8wc64TM9buVSsVBvDT3sYbNwAMQEZnRncV1ECQQDmLOAxO5TX9vef
xpVCF7mYYSByZVLMFQ9VdZNBwk9asJZhEIhwR7FEWT1fUpi28ZEq9lN70GHZZ53M
91mjnY51AkEA1xkYukFVWo9lLJJayZTrAtzVLUL7Ej4odjorwrPPrw+o7B4bAk+I
UyABmjT68zY0Ou93y6bW2vIzZY4e5ZEKHQJAHETsr/9CU5foZ74q/LgPOlDLfGFH
XvtDK9rJ4CyuNFQ10+wE5c1YTy2qpPdu/CEFFEK2lCFOszXPoqnKX5btNQJAJS7I
cIIUwCfjpHXUTd55VbBZBY77mea21eEuaWTt9OQvHkoB/z9CYKQ6wq5/5wUquDln
KwQ3RffyXI7Z1nNhHQJANzJrfEP89nTfDd0tXEIEjU4IbsZxca8xukP+at/KpekO
tDrEwNC4EEtEef2yMRg59jQzacGeZb5jpB09XSDckg==
-----END RSA PRIVATE KEY-----';
       
       $public_key = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDBZkDm6jZ3hGTNVH3XlOSpGBUZ
7Hck1Im3EDUNUm2v94AHBpZj5yGN3XnVVzlzyU6KfFZk/Q36BBKogxPTXrwht7i6
f9teq1Jk03f+umEqTF59Yb+PFDnJKJvSVe8lCQbhJCtxE8DF5Wjf/AY6TVOpu+FW
zaypWak/p7nuCa61QQIDAQAB
-----END PUBLIC KEY-----';
       
       //echo $private_key;
       $pi_key =  openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
       $pu_key = openssl_pkey_get_public($public_key);//这个函数可用来判断公钥是否是可用的
       print_r($pi_key);echo "\n";
       print_r($pu_key);echo "\n";
       
       $data = "aassssasssddd";//原始数据
       $encrypted = "";
       $decrypted = "";
       
       echo "source data:",$data,"\n";
       
       echo "private key encrypt:\n";
       
       openssl_private_encrypt($data,$encrypted,$pi_key);//私钥加密
       $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
       echo $encrypted,"\n";
       
       echo "public key decrypt:\n";
       
       
       openssl_public_decrypt(base64_decode($encrypted),$decrypted,$pu_key);//私钥加密的内容通过公钥可用解密出来
       echo $decrypted,"\n";
       
       echo "---------------------------------------\n";
       echo "public key encrypt:\n";
       
       openssl_public_encrypt($data,$encrypted,$pu_key);//公钥加密
       $encrypted = base64_encode($encrypted);
       echo $encrypted,"\n";
       
       echo "private key decrypt:\n";
       openssl_private_decrypt(base64_decode($encrypted),$decrypted,$pi_key);//私钥解密
       echo $decrypted,"\n";
       
      
      
   // return $css;
   }
   
}
