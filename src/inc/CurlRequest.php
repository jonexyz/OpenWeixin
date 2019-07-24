<?php
/**
 * Created by PhpStorm.
 * User: Jone
 * Date: 2019/7/12
 * Time: 20:07
 *
 */

namespace Jonexyz\inc;

// Curl请求封装
class CurlRequest
{
    /**
     * 发送post请求
     *
     * @param $array 请求的参数
     * @param $timeoutLimit 执行超时时间
     * @param $connectTimeoutLimit 连接超时时间
     * @return mixed
     */
    public static function curl($array, $timeoutLimit, $connectTimeoutLimit)
    {
        $ch = curl_init();
        $url = $array['url'];
        $isHttps = false;
        if (substr($url, 0, 5) == 'https') {//判断是否为https
            $isHttps = true;
        }
        if (isset($array['proxy_type'])) curl_setopt($ch, CURLOPT_PROXY, $array['proxy']);//设置代理
        if (isset($array['proxy_type']) && strpos(strtolower($array['proxy_type']), 'so') !== false) {//socket代理
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        } else {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);//http代理
        }
        curl_setopt($ch, CURLOPT_URL, $array['url']);//设置目标url
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//返回结果，不输出内容
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutLimit);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeoutLimit);

        //设置header
        if (isset($array['header']) && $array['header']) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        }
        if (isset($array['httpheader'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $array['httpheader']);
        }
        //设置referer
        if (isset($array['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $array['referer']);
        }
        //是否走post请求
        if (isset($array['post'])) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $array['post']);
        }
        //是否跟踪重定向
        if (isset($array['followlocation'])) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $array['followlocation']);
        }
        if (isset($array['cookiefile'])) {//cookie文件
            curl_setopt($ch, CURLOPT_COOKIEFILE, $array['cookiefile']);
        }
        if (isset($array['cookiejar'])) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $array['cookiejar']);
        }
        if (isset($val['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $array['cookie']);
        }
        if (isset($array['useragent'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $array['useragent']);
        }
        // 增加https的。不验证证书
        if ($isHttps) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        }
        $html = curl_exec($ch);
        $r['erro'] = curl_error($ch);
        $r['errno'] = curl_errno($ch);
        $r['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $r['html'] = $html;
        curl_close($ch);
        return $r;
    }

    //样例
    //self::curl(['url' => 'https://www.baidu.com'], 1, 1);


    public static function download_img($array, $filename = "")
    {
        $ch = curl_init(); //初始化一个curl句柄
        $hd = fopen($filename, 'wb'); //只写打开或新建一个二进制文件；只允许写数据
        curl_setopt($ch, CURLOPT_URL, $array['url']); //需要获取的 URL 地址
        curl_setopt($ch, CURLOPT_FILE, $hd); //设置成资源流的形式
        curl_setopt($ch, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。
        //curl_setopt($ch,CURLOPT_RETURNTRANSFER,false);//以数据流的方式返回数据,false时直接显示

        if (isset($array['cookiefile'])) {//cookie文件
            curl_setopt($ch, CURLOPT_COOKIEFILE, $array['cookiefile']);
        }
        if (isset($array['cookiejar'])) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $array['cookiejar']);
        }
        if (isset($val['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $array['cookie']);
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, 60); //设置超时时间
        curl_exec($ch); //执行curl
        curl_close($ch); //关闭curl会话
        fclose($hd); //关闭句柄
        return true;
    }

}