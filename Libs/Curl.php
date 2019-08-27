<?php
/**
 *@author longbo
 */
namespace Tesa\Libs;

class Curl
{
    public static $errno = 0;
    public static $error = '';
    public static $httpCode = 0;
    public static $execTime = 0;
    public static $result = 0;

    private $timeout = 60;
    private $header = array();
    private $url = '';
    private $postData = array();
    private $ua = '';
    private $cookie = '';
    private $referer = '';
    private $backHeader = false;

    public function __construct ($url = '') {
        if ($url) {
            $this->url = $url;
        }
        return $this;
    }

    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }

    public function setPostData($data = []) {
        $this->postData = $data;
        return $this;
    }

    public function setUA($uaData) {
        $this->ua = $uaData;
        return $this;
    }

    public function setCookie($cookie) {
        $this->cookie = $cookie;
        return $this;
    }

    public function setReferer($referer) {
        $this->referer = $referer;
        return $this;
    }

    public function setHeader($header) {
        $this->header = $header;
        return $this;
    }

    public function setBackHeader($bool) {
        $this->backHeader = $bool;
        return $this;
    }

    public function setTimeout($sec) {
        $this->timeout = $sec;
        return $this;
    }

    public function get() {
        if(empty($this->url)){
            return $this;
        }
        $stime = microtime(true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->ua);
        curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, $this->backHeader);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        if($this->cookie){
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        }
        if ($this->isHttps()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        self::$result = curl_exec($ch);
        self::$errno = curl_errno($ch);
        self::$error = curl_error($ch);
        self::$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $etime = microtime(true);
        self::$execTime = round(($etime - $stime) * 1000, 3);
        return $this;
    }

    public function post() {
        if (empty($this->url)) {
            return $this;
        }
        $stime = microtime(true);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->postData));
        curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, $this->backHeader);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        if($this->cookie){
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        }
        if ($this->isHttps()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        self::$result = curl_exec($ch);
        self::$errno = curl_errno($ch);
        self::$error = curl_error($ch);
        self::$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $etime = microtime(true);
        self::$execTime = round(($etime - $stime) * 1000, 3);
        return $this;
    }

    private function isHttps() {
        return substr($this->url, 0, 5) === 'https';
    }

}
