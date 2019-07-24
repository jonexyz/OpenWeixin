<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/20
 * Time: 16:24
 */
namespace Jonexyz;

use Jonexyz\inc\StrTool;
use Jonexyz\inc\Api;

class OpenWechat
{
    private $cookie_path;  // cookie 存储路径
    private $file_path;    // 用户临时数据存储路径

    private $username;   // 用户账号
    private $password;   // 用户密码

    private $api;   // 存储API类实例化的对象

    public function __construct($username,$password)
    {
        $curr_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.$username;

        $this->cookie_path = $curr_path.DIRECTORY_SEPARATOR.'cookie.cookie';
        $this->file_path = $curr_path.DIRECTORY_SEPARATOR.'token.txt';

        $this->username = $username;
        $this->password = $password;

        if(!is_dir($curr_path)){
            mkdir($curr_path);
        }

        $this->api = new Api($this->cookie_path);

    }


    /**
     * 执行登录操作
     * @return array
     */
    public function do_login()
    {
        $res_home = $this->api->url_home();

        if (stripos($res_home['html'], $this->username) == false) {

            $res_login = $this->api->url_login($this->username, $this->password);
            $html = json_decode($res_login['html'],true);

            if(isset($html['base_resp']['token']) && $token = $html['base_resp']['token']){

                $this->api->set_token($token);
                file_put_contents($this->file_path,$token);
                return $this->returnData(1,'登录成功', ['token'=>$token] );
            }

            return $this->returnData(0,'登录失败'  );

        } else {
            if (!is_file($this->file_path)) {

                if (is_file($this->cookie_path)) {
                    unlink($this->cookie_path);
                    $res = $this->do_login();
                    return $res;
                }

            } else {
                $token = file_get_contents($this->file_path);
                $this->api->set_token($token);
                return $this->returnData(1,'登录成功', ['token'=>$token] );
            }

        }

    }


    /**
     * 执行退出登录操作
     * @return array
     */
    public function do_loginOut()
    {
        $res = $this->api->url_loginOut();

        if (stripos($res['html'], $this->username) == false){
            $return_data = $this->returnData(1,'退出成功','');
        }else{
            $return_data = $this->returnData(0,'退出失败', '');
        }

        return $return_data;
    }


    /**
     * 获取应用列表
     * @return array
     */
    public function do_getAppList()
    {
        $res = $this->api->url_appList();

        if($res['html']){
            preg_match("/applist([^;]*)/", $res['html'], $c);

            //var_dump($c);die;
            preg_match_all("/\[\{([^\]]*)]/", $c[1], $cd);

            $appid_list = json_decode($cd[0][1], true);

            return $this->returnData(1,'获取应用列表成功', $appid_list);  // 获取应用列表

        }else{

            return $this->returnData(0,'应用列表获取失败，请稍后再试');
        }


    }

    /**
     * 创建应用
     * @param $name  string 应用名称
     * @param $sns_domain  string 开发域名
     * @param $white_acct  string  原始id
     * @param $white_ip  string  授权ip
     * @param $msg_url   string 授权事件接收url
     * @param $ticket_url  string  消息与事件接收url
     * @param null $auth_domain  string  授权发起域名
     * @return array|mixed
     */
    public function do_appCreate($name,$sns_domain,$white_acct,$white_ip,$msg_url,$ticket_url,$auth_domain=null)
    {
        if(empty($auth_domain))$auth_domain = $_SERVER["HTTP_HOST"];

        $d = [
            'name' => $name,  //应用名
            "desc" => StrTool::random(rand(5,20),2),  //应用描述
            'official_site' => StrTool::domain(),  // 应用官网
            'auth_token' => StrTool::random(rand(3,20)),  // 消息校验Token
            'auth_domain' => $auth_domain, //登录授权的发起页域名
            'ticket_url' => $ticket_url,  //授权事件接收URL
            'msg_url' => $msg_url,  //消息与事件接收URL
            'white_ip' => $white_ip,  //白名单IP地址列表
            'white_acct' => $white_acct,  //授权测试公众号列表
            'symmetric_key' => StrTool::random(43),  //消息加解密Key
            'sns_domain' => $sns_domain, //公众号开发域名
        ];

        // 下载验证文件
        $res = $this->api->url_downloadCheckFile();

        //匹配出名称
        preg_match("/=([^\.]*)/", $res['html'], $back);
        //分割出内容
        $res_body = explode("\n\r",$res['html']);

        //保存文件到文件验证类
        $verify_file_path = './';
        if(!is_file($verify_file_path . $back[1].'txt'))
        file_put_contents($verify_file_path . $back[1] . ".txt", trim($res_body[1]));


        //校验应用标题
        $check_name_res = $this->api->url_checkName($d['name']);
        if($check_name_res){
            //var_dump($check_name_res);die;
        }


        // 校验应用原始ID


        // 校验应用业务域名
        $check_domain_res = $this->api->url_checkDomain($d['sns_domain']);
        if($check_domain_res){
           // var_dump($check_domain_res);die;
        }


        // 创建应用
        $res = $this->api->url_appCreate($d);
        $res_data = json_decode($res['html'],true);
        if(isset($res_data['base_resp']) && $res_data['base_resp']['err_msg'] == "ok" && $res_data['err_msg'] == "ok"){

            $app_list_res = $this->do_getAppList();

            if($app_list_res['code']){
                $appid = '';
                foreach ($app_list_res['data'] as $value ){
                    if($value['name'] == $name){
                        $appid = $value['appid'];
                    }
                }

                if($appid){
                    return $this->returnData(1,'应用创建成功，返回APPID字段数据', $appid);
                }else{
                    return $this->returnData(0,'应用创建成功，请登录微信开放平台获取');
                }

            }else{
                return $this->returnData(0,'获取应用APPID失败，请登录微信开放平台获取');
            }

        }else{
            return $this->returnData(0,'应用创建失败');
        }

    }


    /**
     * 修改应用
     * @param $name  string 应用名称
     * @param $sns_domain  string 开发域名
     * @param $white_acct  string  原始id
     * @param $white_ip  string  授权ip
     * @param $msg_url   string 授权事件接收url
     * @param $ticket_url  string  消息与事件接收url
     * @param null $auth_domain  string  授权发起域名
     * @param $appid string  应用appid
     * @return array|mixed
     */
    public function do_updateApp($name,$sns_domain,$white_acct,$white_ip,$msg_url,$ticket_url,$appid,$auth_domain=null)
    {
        if(empty($auth_domain))$auth_domain = $_SERVER["HTTP_HOST"];

        $d = [
            'name' => $name,
            'desc' => StrTool::random(rand(5,15),2),
            'official_site' => StrTool::domain(),
            'auth_token' => StrTool::random(10),
            'auth_domain' => $auth_domain,
            'ticket_url' => $ticket_url,
            'msg_url' =>  $msg_url,
            'white_ip' => $white_ip,
            'white_acct' => $white_acct,
            'symmetric_key' => StrTool::random(43),
            'sns_domain' => $sns_domain,
            'appid' => $appid,
            'token' => $this->token,
        ];

        //校验应用标题
        $check_name_res = $this->api->url_checkName($d['name']);
        if($check_name_res){
            //var_dump($check_name_res);die;
        }


        // 校验应用原始ID


        // 校验应用业务域名
        $check_domain_res = $this->api->url_checkDomain($d['sns_domain']);
        if($check_domain_res){
            // var_dump($check_domain_res);die;
        }

        $res = $this->api->url_appUpdate($d);


        return $this->returnData(1,'');
    }


    /**
     * 获取当月应用开发域名剩余修改次数
     * @param $appid
     * @return
     */
    public function do_getDomainEditNum($appid)
    {
        $res = $this->api->url_getDomainEditNum($appid);

        if($res['html']){
            preg_match("/(\d)次机会/", $res['html'], $m);

            return $this->returnData(1,'业务域名剩余修改次数获取成功', $m[1] );
        }else{
            return $this->returnData(0,'业务域名剩余修改次数获取失败' );
        }

    }


    /**
     * @param $appid  应用appid
     * @param $filename string 二维码图片存储路径
     * @return
     */
    public function do_getQrcode($appid,$filename)
    {
        $res_ticket = $this->api->url_getQrcheckTicket($appid);

        $res_ticket = json_decode($res_ticket['html'],true);

        if(empty($res_ticket['qrcheck_ticket'])){
            return $this->returnData(0,'请检查是否进行了登录操作，或者 appid对应应用是否存在');
        }

        $dowm_qrcode = $this->api->url_getQrcode($appid,$res_ticket['qrcheck_ticket'],$filename);

        if($dowm_qrcode){
            return $this->returnData(1,'二维码下载成功', $filename);
        }else{
            return $this->returnData(0, '二维码下载失败');
        }
    }


    /**
     * 检查二维码扫描是否成功
     * @param $appid
     * @return array
     */
    public function do_ask_code($appid)
    {
        $res_ticket = $this->api->url_getQrcheckTicket($appid);

        $res_ticket = json_decode($res_ticket['html'],true);

        if(empty($res_ticket['qrcheck_ticket'])){
            return $this->returnData(0,'请检查是否进行了登录操作，或者 appid对应应用是否存在');
        }


        $res_code_ask = $this->api->url_code_ask($appid,$res_ticket['qrcheck_ticket']);

        if(empty(json_decode($res_code_ask['html'],true)['extra']['wx_id'])){

            return $this->returnData(0,'扫描失败，请重新操作');
        }

        $wx_id = json_decode($res_code_ask['html'],true)['extra']['wx_id'];

        return $this->returnData(1,'授权成功,返回授权',$wx_id);
    }

    /**
     * 获取 Appsecret
     */
    public function do_getAppsecret($appid)
    {
        $res_ticket = $this->api->url_getQrcheckTicket($appid);

        $res_ticket = json_decode($res_ticket['html'],true);

        if(empty($res_ticket['qrcheck_ticket'])){
            return $this->returnData(0,'请检查是否进行了登录操作，或者 appid对应应用是否存在');
        }

        $res = $this->api->url_getAppsecret($appid,$res_ticket['qrcheck_ticket']);

        if(empty(json_decode($res['html'],true)['secret'])){
            return  $this->returnData(0,'获取secret失败');
        }

        $secret = json_decode($res['html'],true)['secret'];

        return $this->returnData(1,'获取secret成功',$secret);
    }

    /**
     * @param int  $code 状态码 [ 1成功； 0失败 ]
     * @param string $msg  状态描述
     * @param $data  需要返回的数据
     * @return array  拼接成数组并返回
     */
    public function returnData(int $code,string $msg, $data=null)
    {
        return ['code'=>$code, 'msg'=>$msg, 'data'=>$data];
    }

}