<?php
namespace BBExtend\common;

/*
 * author: Solon Ring
 * time: 2011-11-02
 * 发博时间计算(年，月，日，时，分，秒)
 * $createtime 可以是当前时间
 * $gettime 你要传进来的时间
 */

class Mygettime
{

    function  __construct($gettime) {
        $this->createtime = time();
        $this->gettime = $gettime;
    }

    function getSeconds()
    {
        return $this->createtime-$this->gettime;
    }

    function getMinutes()
    {
        return ($this->createtime-$this->gettime)/(60);
    }

    function getHours()
    {
        return ($this->createtime-$this->gettime)/(60*60);
    }

    function getDay()
    {
        return ($this->createtime-$this->gettime)/(60*60*24);
    }

    function getWeek()
    {
        return ($this->createtime-$this->gettime)/(60*60*24*7);
    }

    function getMonth()
    {
        return ($this->createtime-$this->gettime)/(60*60*24*30);
    }

    function getYear()
    {
        return ($this->createtime-$this->gettime)/(60*60*24*30*12);
    }

    function display()
    {
        if($this->getYear() > 1)
        {
//             if($this->getYear() > 2)
//             {
//                 return date("Y-m-d",$this->gettime);
                
//             }
            return intval($this->getYear())." 年前";

        }

        if($this->getMonth() > 1)
        {
            return intval($this->getMonth())." 月前";
            //exit();
        }

        if($this->getWeek() > 1)
        {
            return intval($this->getWeek())." 周前";
            //exit();
        }

        if($this->getDay() > 1)
        {
            return intval($this->getDay())." 天前";
            //exit();
        }

        if($this->getHours() > 1)
        {
            return intval($this->getHours())." 小时前";
            //exit();
        }

        if($this->getMinutes() > 1)
        {
            return intval($this->getMinutes())." 分钟前";
            //exit();
        }

        if($this->getSeconds() > 1)
        {
            return intval($this->getSeconds()-1)." 秒前";
            //exit();
        }

    }

}