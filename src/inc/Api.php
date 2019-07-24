<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/22
 * Time: 15:55
 */
namespace Jonexyz\inc;


class Api
{
    private $cookie_path;
    private $token;
 
    public function __construct($cookie_path)
    {
        $this->cookie_path = $cookie_path;
    }

    /**
     * 设置属性 token 值
     */
    public function set_token($token)
    {
        $this->token = $token;
    }

    /**
     * 首页访问接口
     * @return mixed
     */
    public function url_home()
    {
        $url = 'https://open.weixin.qq.com';

        $data = $this->requestData($url);

        $res = CurlRequest::curl($data,10,10);

        return $res;
    }



    /**
     * 请求登录接口
     * @return mixed
     */
    public function url_login($username, $password)
    {
        $url = 'https://open.weixin.qq.com/cgi-bin/login';
        $post = [
            'account' => $username,
            'passwd' => md5($password),
            'lang' => 'zh_CN',
            'f' => 'json',
            'ajax' => 1,
            'key' => '1'
        ];

        $referer = 'https://open.weixin.qq.com/';

        $data = $this->requestData($url,$post,$referer);
        $res = CurlRequest::curl($data,10,10);

        return $res;
    }


    /**
     * 退出登录接口
     * @return mixed
     */
    public function url_loginOut()
    {
        $url = 'https://open.weixin.qq.com/cgi-bin/logout';
        $referer = 'https://open.weixin.qq.com/';

        $data = $this->requestData($url,[],$referer);

        $res = CurlRequest::curl($data,10,10);

        return $res;
    }


    /**
     * 已创建应用列表
     * @return string  返回网页内容
     */
    public function url_appList()
    {
        $url = "https://open.weixin.qq.com/cgi-bin/applist?t=manage/list&page=0&num=20&openapptype=2048&token=" . $this->token . "&lang=zh_CN";

        $data = $this->requestData($url);

        $res = CurlRequest::curl($data,50,50);
        
        return $res;
    }


    /**
     * 创建应用
     * @param $d array 创建应需要的参数
     * @return array
     */
    public function url_appCreate($d)
    {
        $url = "https://open.weixin.qq.com/cgi-bin/component_acct";

        $referer = "https://open.weixin.qq.com/cgi-bin/component_acct?t=manage/createBizPlugin&action=create&lang=zh_CN&token=" . $this->token;

        $post = [
            'name' => $d['name'],  //应用名
            "desc" => $d['desc'],  //应用描述
            'official_site' => $d['official_site'],  // 应用官网
            'icon_url' => "http://mmbiz.qpic.cn/mmbiz_png/VPtJAWJntQ99y1xdSoHWyBMjtG4YHjc3zpiaeGgdmqPf4zvaFkjicibz1iaOWPuTT2jU4L9wW4ia3SQ9NniaTY30Nk5Q/0?wx_fmt=png",  // 应用图标
            'auth_token' => $d['auth_token'],  // 消息校验Token
            'auth_domain' => $d['auth_domain'], //登录授权的发起页域名
            'ticket_url' => $d['ticket_url'],  //授权事件接收URL
            'msg_url' => $d['msg_url'],  //消息与事件接收URL
            'white_ip' => $d['white_ip'],  //白名单IP地址列表
            'white_acct' => $d['white_acct'],  //授权测试公众号列表
            'category_list' => '4',
            'symmetric_key' => $d['symmetric_key'],  //消息加解密Key
            'sns_domain' => $d['sns_domain'], //公众号开发域名
            'tag_id_list' => "1001",
            'token' => $this->token,
            'f' => "json",
            'ajax' => '1',
            'action' => 'create',
            'key' => 'create',
            //"white_mp" => $gzhlist
        ];
        //var_dump($send_body);die;

        $data = $this->requestData($url,$post,$referer);

        $res = CurlRequest::curl($data, 10, 10);

        return $res;
    }


    /**
     * 检验应用名称是否可用
     * @param $oname string 应用名称
     * @return mixed
     */
    public function url_checkName($oname)
    {
        $referer = "https://open.weixin.qq.com/cgi-bin/component_acct?t=manage/createBizPlugin&action=create&lang=zh_CN&token=" . $this->token;

        $post = [
            'name' => $oname,
            'token' => $this->token,
            'lang' => 'zh_CN',
            'f' => 'json',
            'ajax' => '1',
            'action' => 'check_name',
            'key' => 'check_name'
        ];

        $url = "https://open.weixin.qq.com/cgi-bin/component_acct";

        $data = $this->requestData($url,$post,$referer);

        $res = CurlRequest::curl($data,10,10);

        return $res;
    }


    /**
     * 校验开发域名
     * @param  $sns_domain string  开放平台开发域名
     * @return
     */
    public function url_checkDomain($sns_domain)
    {
        $url = "https://open.weixin.qq.com/cgi-bin/component_acct";

        $post = [
            'sns_domain' => $sns_domain,
            'token' => $this->token,
            'lang' => 'zh_CN',
            'f' => 'json',
            'ajax' => 1,
            'action' => 'check_domain_by_scene',
            'key' => 'check_domain_by_scene'
        ];

        $referer = "https://open.weixin.qq.com/cgi-bin/component_acct?t=manage/createBizPlugin&action=create&lang=zh_CN&token=" . $this->token;

        $data = $this->requestData($url,$post,$referer);

        $res = CurlRequest::curl($data,5,5);

        return $res;
    }


    /**
     * 获取校验文件
     */
    public function url_downloadCheckFile()
    {
        $url = "https://open.weixin.qq.com/cgi-bin/component_acct?action=download_confirmfile&token=" . $this->token . "&lang=zh_CN";

        $data = $this->requestData($url,[],null,true);

        $res = CurlRequest::curl($data,10,10);

        return $res;
    }


    /**
     * 修改应用
     * @param $d array 修改应需要的参数
     * @return array
     */
    public function url_appUpdate($d)
    {
        $icon_url = "http://mmbiz.qpic.cn/mmbiz_png/VPtJAWJntQ99y1xdSoHWyBMjtG4YHjc3zpiaeGgdmqPf4zvaFkjicibz1iaOWPuTT2jU4L9wW4ia3SQ9NniaTY30Nk5Q/0?wx_fmt=png";
        $referer = "https://open.weixin.qq.com/cgi-bin/component_acct?t=manage/createBizPlugin&action=create&lang=zh_CN&token=" . $this->token;

        $url = "https://open.weixin.qq.com/cgi-bin/component_acct";

        $send_body = [
            'name' => $d['name'],
            'desc' => $d['desc'],
            'official_site' => $d['official_site'],
            'icon_url' => $icon_url,
            'auth_token' => $d['auth_token'],
            'auth_domain' => $d['auth_domain'],
            'ticket_url' => $d['ticket_url'],
            'msg_url' =>  $d['msg_url'],
            'white_ip' => $d['white_ip'],
            'white_acct' => $d['white_acct'],
            'category_list' => '4',
            'symmetric_key' => $d['symmetric_key'],
            'sns_domain' => $d['sns_domain'],
            'tag_id_list' => '1001',
            'appid' => $d['appid'],
            'token' => $this->token,
            'lang' => 'zh_CN',
            'f' => 'json',
            'ajax' => '1',
            'action' => 'modify',
            'key' => 'modify'
        ];
        //dd($send_body);

        $data = $this->requestData($url, $send_body, $referer);

        $res = CurlRequest::curl($data, 10,10);

        $backdata = json_decode($res['html'], true);
        if ($backdata['base_resp']['err_msg'] != "ok" || $backdata['err_code'] != "0") {
            //执行失败
            //echo $b->body;
            if($backdata['base_resp']['err_msg']=='ok' && $backdata['err_code'] == 1){
                return ['status'=>0,'msg'=>'业务绑定域名修改次数为0，或者域名被封'];
            }
            if($backdata['base_resp']['err_msg']=='ok' && $backdata['err_code'] == 1003){
                return ['status'=>0,'msg'=>'原始id填写不正确'];
            }

            return ['status'=>0,'msg'=>'应用信息更新失败'.$res['html']];


        }else{

            return ['status'=>1,'msg'=>'应用ID'.$d['id'].'数据同步成功','data'=>$send_body];
        }
    }


    /**
     * @param $appid string 应用appid
     * @return array
     */
    public function url_getDomainEditNum($appid)
    {
        $url = "https://open.weixin.qq.com/cgi-bin/component_acct?action=modify&t=manage/plugin_modify&appid=" . $appid . "&token=" . $this->token;

        $data = $this->requestData($url);

        $res = CurlRequest::curl($data);

        return $res;

    }


    /**
     * 获取 qrcheck_ticket 参数，调取二维码验证需要的参数
     * @param $appid
     * @return mixed
     */
    public function url_getQrcheckTicket($appid)
    {
        $url = 'https://open.weixin.qq.com/cgi-bin/authqr';

        $post = [
            'action' => 'get',
            'typeid' => 1,
            'appid' => $appid,
            'token' => $this->token,
            'f' => 'json',
            'key' => 'get',
            'size' => 120,
            'scene' => 10,
        ];

        $refer = 'https://open.weixin.qq.com/wxaopen/serviceprovider/'.$appid.'?token='.$this->token;

        $data = $this->requestData($url,$post,$refer);

        $res = CurlRequest::curl($data,10,10);
        return $res;
    }


    /**
     * 下载二维码
     * @param $appid
     * @param $qrcheck_ticket
     * @return bool
     */
    public function url_getQrcode($appid,$qrcheck_ticket,$filename)
    {

        $url = 'https://open.weixin.qq.com/cgi-bin/authqr?action=getqrcode&qrcheck_ticket='.$qrcheck_ticket.'&size120&token='.$this->token;

        $refer = 'https://open.weixin.qq.com/wxaopen/serviceprovider/'.$appid.'?token='.$this->token;

        $data = $this->requestData($url,null,$refer);

        $res =  CurlRequest::download_img($data,$filename);

        return $res;
    }


    /**
     * 获取 secretkey 接口
     * @param $appid
     * @param $qrcheck_ticket
     * @return array
     */
    public function url_getAppsecret($appid,$qrcheck_ticket)
    {
        $url = 'https://open.weixin.qq.com/cgi-bin/appdetail';
        $post = [
            'appid'=>$appid,
            'qr_ticket'=>$qrcheck_ticket,
            'action'=>'open_refresh_secretkey',
            'token'=>$this->token
        ];

        $refer = 'https://open.weixin.qq.com/wxaopen/serviceprovider/'.$appid.'?token='.$this->token;

        $data = $this->requestData($url,$post,$refer);

        $res = CurlRequest::curl($data,10,10);

        return $res;
    }


    /**
     * 检验二维码扫描是否成功
     * @param $appid
     * @param $qrcheck_ticket string
     * @return
     */
    public function url_code_ask($appid, $qrcheck_ticket )
    {

        $url = 'https://open.weixin.qq.com/cgi-bin/authqr?action=ask&qrcheck_ticket='.$qrcheck_ticket.'&key=ask&token='.$this->token.'&f=json';

        $refer ='https://open.weixin.qq.com/wxaopen/serviceprovider/'.$appid.'?token='.$this->token;

        $data = $this->requestData($url, null, $refer);

        $res = CurlRequest::curl($data,10 ,10);

        return $res;



    }





    public function url_checkWhite_acct()
    {
        // TODO: Implement checkWhite_acct() method.
    }




    /**
     * 生成 curl方法需要的参数
     * @param $url  string 请求地址
     * @param array $post   请求参数
     * @param string $referer   请求referer
     * @param bool $header 是否输出响应头
     * @return array  返回数组
     */
    public function requestData($url,$post=[],$referer='', $header=false)
    {
        $data = [
            'url' => $url,
            'cookiefile' => $this->cookie_path,
            'cookiejar' => $this->cookie_path,
            'useragent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
        ];

        if($post) $data['post'] = $post;
        if($referer) $data['referer'] = $referer;
        if($header)$data['header'] = 1;

        return $data;
    }

}