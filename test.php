<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/12
 * Time: 20:54
 */

require './vendor/autoload.php';

use Jonexyz\CurlRequest;

 $res = CurlRequest::curl(['url'=>'https://baidu.com',],10,1);

 var_dump($res);