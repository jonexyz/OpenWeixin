# OpenWeixin
微信开放平台创建第三方平台应用

composer安装

composer require jonexyz/open_weixin dev-master

````
include './vendor/autoload.php';

use Jonexyz\OpenWechat;

$res = new OpenWechat('账号','密码'); //实例化对象

$res->do_login(); //执行登录操作

var_dump($res->do_getAppList());  // 获取应用列表

````

