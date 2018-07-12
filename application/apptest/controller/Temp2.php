<?php
namespace app\apptest\controller;


class Temp2 
{
    
   //程序入口。  
   public function index()
   {
      $arr=[30,45,3,1,6,3,66];
      $this->MergeSort($arr);
      
      dump($arr);
   }
   
   
  
   
   //归并算法总函数
   function MergeSort(array &$arr){
       $start = 0;
       $end = count($arr) - 1;
       $this->MSort($arr,$start,$end);
   }
   
   //递归分治，归并
   function MSort(array &$arr,$start,$end){
       //当子序列长度为1时，$start == $end，不用再分组
       if($start < $end){
           $mid = floor(($start + $end) / 2);	//将 $arr 平分为 $arr[$start - $mid] 和 $arr[$mid+1 - $end]
           $this->MSort($arr,$start,$mid);			//将 $arr[$start - $mid] 归并为有序的$arr[$start - $mid]
           $this->MSort($arr,$mid + 1,$end);			//将 $arr[$mid+1 - $end] 归并为有序的 $arr[$mid+1 - $end]
           $result = $this->Merge($arr,$start,$mid,$end);       //将$arr[$start - $mid]部分和$arr[$mid+1 - $end]部分合并起来成为有序的$arr[$start - $end]
           $i = $start;
           foreach ( $result as $v ) {
               $arr[$i++] = $v;
           }
       }
   }
   
   // 单独的归并算法，不含分治。
   function Merge(array $arr,$start,$mid,$end)
   {
       
       $temparr = [];
       
       //根据 下标 截取成两个数组。
       $arr_left = array_slice($arr, $start, $mid - $start + 1);
       $arr_right = array_slice($arr, $mid + 1,  $end - $mid );
       
       while ($arr_left || $arr_right ) { // 只有left数组和right数组任何一个不空，请不停继续下去。
           if ( $arr_left && $arr_right ) { // 如果 两个数组都有值，则取头部的最小值放到临时数组，并删除这个值在原数组中。
               if ($arr_left[0] < $arr_right[0]  ) {
                   $temparr []= array_shift( $arr_left );  //弹出第一个并保存
               }else {
                   $temparr []= array_shift( $arr_right );//弹出第一个并保存，包括相等的情况。
               }
           } elseif (!$arr_left) { // 剩余两个else 的存在意义：两个数组总有一方先取完，那就不需要比较了，于是取剩余的放到临时数组里。
               $temparr []= array_shift($arr_right);
           } else {
               $temparr []= array_shift($arr_left);
           }
       }
       return $temparr;
   }
   
}




