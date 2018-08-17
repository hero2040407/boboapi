<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/17 0017
 * Time: 上午 11:02
 */
namespace app\backstage\controller;

Interface CommonInterface
{
    function index();

    function read();

    function update();

    function create();

    function delete();
}