<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/12
 * Time: 20:54
 */

require './vendor/autoload.php';

use Jonexyz\OpenWechat;

$res = (new OpenWechat('***@163.com','***'));

$res->do_login();


$r=$res->do_getQrcode('wxf3ee6304c5e8096d','img.jpg');

while (true){
    $r = $res->do_ask_code('wxf3ee6304c5e8096d');
    if($r['code']){
        break;
    }
}



$tt = $res->do_getAppsecret('wxf3ee6304c5e8096d');

var_dump($tt);
